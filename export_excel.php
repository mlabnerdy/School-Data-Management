<?php

require_once __DIR__ . '/config.php';

require_login();

$role = $_SESSION['role'] ?? '';

$type     = $_POST['type'] ?? '';
$password = $_POST['password'] ?? '';

$isAdmin   = strcasecmp($role, 'Administrator') === 0;
$isStaff   = strcasecmp($role, 'Staff') === 0;
$isTeacher = strcasecmp($role, 'Teacher') === 0;


/*
|--------------------------------------------------------------------------
| JSON ERROR RESPONSE
|--------------------------------------------------------------------------
*/

function exportError($message, $status = 400)
{
    http_response_code($status);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'message' => $message
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK PERMISSION
|--------------------------------------------------------------------------
*/

$allowed = false;

if ($type === 'students') {

    $allowed = $isTeacher || $isStaff || $isAdmin;

} elseif ($type === 'teachers') {

    $allowed = $isStaff || $isAdmin;

} elseif ($type === 'staff') {

    $allowed = $isAdmin;
}


if (!$allowed) {

    exportError(
        'You are not authorized to export this data.',
        403
    );
}


/*
|--------------------------------------------------------------------------
| CHECK PASSWORD
|--------------------------------------------------------------------------
*/

if ($password === '') {

    exportError(
        'Password is required.'
    );
}


$stmt = $pdo->prepare("
    SELECT password
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $_SESSION['user_id']
]);

$user = $stmt->fetch();


if (
    !$user ||
    !password_verify(
        $password,
        $user['password']
    )
) {

    exportError(
        'Incorrect password. Export cancelled.'
    );
}


/*
|--------------------------------------------------------------------------
| SELECT TABLE
|--------------------------------------------------------------------------
*/

switch ($type) {

    case 'students':

        $table = 'students';

        $filename =
            'students_export_' .
            date('Y-m-d_H-i-s') .
            '.xlsx';

        $sheetName = 'Students';

        break;


    case 'teachers':

        $table = 'teachers';

        $filename =
            'teachers_export_' .
            date('Y-m-d_H-i-s') .
            '.xlsx';

        $sheetName = 'Teachers';

        break;


    case 'staff':

        $table = 'staff';

        $filename =
            'staff_export_' .
            date('Y-m-d_H-i-s') .
            '.xlsx';

        $sheetName = 'Staff';

        break;


    default:

        exportError(
            'Invalid export type.'
        );
}


/*
|--------------------------------------------------------------------------
| GET DATABASE COLUMNS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SHOW COLUMNS FROM `$table`
");

$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (!$columns) {

    exit('No columns found.');

}


/*
|--------------------------------------------------------------------------
| EXCLUDED COLUMNS
|--------------------------------------------------------------------------
|
| These will NOT appear in Excel.
|
*/

$excludedColumns = [
    'id',
    'user_id',
    'email',
    'photo',
    'created_at',
    'updated_at'
];

$exportColumns = [];


foreach ($columns as $column) {

    $field = strtolower($column['Field']);

    if (in_array($field, $excludedColumns, true)) {

        continue;

    }

    $exportColumns[] = $column;

}


/*
|--------------------------------------------------------------------------
| GET RECORDS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT *
    FROM `$table`
    ORDER BY id ASC
");

$records = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| PHILIPPINE PHONE FORMAT
|--------------------------------------------------------------------------
*/

function formatPhilippineNumber($number)
{
    if ($number === null) {

        return '';

    }

    $number = trim((string)$number);

    if ($number === '') {

        return '';

    }


    /*
    |--------------------------------------------------------------------------
    | Remove unwanted characters
    |--------------------------------------------------------------------------
    */

    $number = preg_replace(
        '/[^0-9+]/',
        '',
        $number
    );


    /*
    |--------------------------------------------------------------------------
    | +639XXXXXXXXX
    |--------------------------------------------------------------------------
    */

    if (strpos($number, '+63') === 0) {

        $digits = substr($number, 3);

        if (
            strlen($digits) === 10 &&
            $digits[0] === '9'
        ) {

            return '+63 ' .
                substr($digits, 0, 3) . ' ' .
                substr($digits, 3, 3) . ' ' .
                substr($digits, 6, 4);

        }

        return $number;

    }


    /*
    |--------------------------------------------------------------------------
    | 639XXXXXXXXX
    |--------------------------------------------------------------------------
    */

    if (
        strpos($number, '63') === 0 &&
        strlen($number) === 12
    ) {

        $digits = substr($number, 2);

        if ($digits[0] === '9') {

            return '+63 ' .
                substr($digits, 0, 3) . ' ' .
                substr($digits, 3, 3) . ' ' .
                substr($digits, 6, 4);

        }

    }


    /*
    |--------------------------------------------------------------------------
    | 09XXXXXXXXX
    |--------------------------------------------------------------------------
    */

    if (
        strlen($number) === 11 &&
        strpos($number, '09') === 0
    ) {

        $digits = substr($number, 1);

        return '+63 ' .
            substr($digits, 0, 3) . ' ' .
            substr($digits, 3, 3) . ' ' .
            substr($digits, 6, 4);

    }


    /*
    |--------------------------------------------------------------------------
    | 9XXXXXXXXX
    |--------------------------------------------------------------------------
    */

    if (
        strlen($number) === 10 &&
        $number[0] === '9'
    ) {

        return '+63 ' .
            substr($number, 0, 3) . ' ' .
            substr($number, 3, 3) . ' ' .
            substr($number, 6, 4);

    }


    return $number;
}


/*
|--------------------------------------------------------------------------
| XML ESCAPE
|--------------------------------------------------------------------------
*/

function xlsxXml($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| EXCEL COLUMN LETTER
|--------------------------------------------------------------------------
*/

function excelColumn($number)
{
    $letter = '';

    while ($number > 0) {

        $mod = ($number - 1) % 26;

        $letter =
            chr(65 + $mod) .
            $letter;

        $number =
            (int)(($number - $mod) / 26) - 1;

    }

    return $letter;
}


/*
|--------------------------------------------------------------------------
| EXCEL CELL REFERENCE
|--------------------------------------------------------------------------
*/

function cellReference($column, $row)
{
    return excelColumn($column) . $row;
}


/*
|--------------------------------------------------------------------------
| COLUMN WIDTH
|--------------------------------------------------------------------------
*/

function getColumnWidth($field)
{
    $field = strtolower($field);


    $widths = [

        'lrn' => 18,

        'full_name' => 28,

        'date_of_birth' => 15,

        'gender' => 12,

        'address' => 35,

        'contact_number' => 22,

        'contact' => 22,

        'phone' => 22,

        'phone_number' => 22,

        'parent_guardian' => 28,

        'grade_section' => 24,

        'other_info' => 30,

        'school_id' => 14,

        'school_year' => 15,

        'emergency_name' => 28,

        'emergency_address' => 35,

        'emergency_contact' => 22,

        'emergency_number' => 22

    ];


    if (isset($widths[$field])) {

        return $widths[$field];

    }


    return 20;
}


/*
|--------------------------------------------------------------------------
| ZIP FILE CREATOR
|--------------------------------------------------------------------------
|
| This creates a valid ZIP file using PHP only.
|
| XLSX files are ZIP packages containing XML files.
|
| No Composer.
| No PhpSpreadsheet.
| No ext-zip.
|
|--------------------------------------------------------------------------
*/

function createZipFile($files)
{
    $data = '';

    $centralDirectory = '';

    $offset = 0;

    $fileCount = 0;


    foreach ($files as $filename => $content) {

        $filenameBytes = $filename;

        $contentBytes = $content;

        $crc = crc32($contentBytes);

        /*
        |--------------------------------------------------------------------------
        | Convert signed CRC32 to unsigned
        |--------------------------------------------------------------------------
        */

        if ($crc < 0) {

            $crc += 4294967296;

        }


        $compressedData =
            $contentBytes;


        $compressedSize =
            strlen($compressedData);

        $uncompressedSize =
            strlen($contentBytes);


        $filenameLength =
            strlen($filenameBytes);


        /*
        |--------------------------------------------------------------------------
        | Local File Header
        |--------------------------------------------------------------------------
        */

        $localHeader =
            pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0,
                0,
                0,
                0,
                $crc,
                $compressedSize,
                $uncompressedSize,
                $filenameLength,
                0
            );


        $localHeader .=
            $filenameBytes;


        $data .=
            $localHeader .
            $compressedData;


        /*
        |--------------------------------------------------------------------------
        | Central Directory Header
        |--------------------------------------------------------------------------
        */

        $centralHeader =
            pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0,
                0,
                0,
                0,
                $crc,
                $compressedSize,
                $uncompressedSize,
                $filenameLength,
                0,
                0,
                0,
                0,
                0,
                $offset
            );


        $centralDirectory .=
            $centralHeader .
            $filenameBytes;


        $offset =
            strlen($data);

        $fileCount++;

    }


    /*
    |--------------------------------------------------------------------------
    | End Of Central Directory
    |--------------------------------------------------------------------------
    */

    $centralDirectoryOffset =
        strlen($data);

    $centralDirectorySize =
        strlen($centralDirectory);


    $endRecord =
        pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $fileCount,
            $fileCount,
            $centralDirectorySize,
            $centralDirectoryOffset,
            0
        );


    return
        $data .
        $centralDirectory .
        $endRecord;
}


/*
|--------------------------------------------------------------------------
| CONTENT TYPES
|--------------------------------------------------------------------------
*/

$contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$contentTypes .= '
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">

    <Default
        Extension="rels"
        ContentType="application/vnd.openxmlformats-package.relationships+xml"
    />

    <Default
        Extension="xml"
        ContentType="application/xml"
    />

    <Override
        PartName="/xl/workbook.xml"
        ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"
    />

    <Override
        PartName="/xl/worksheets/sheet1.xml"
        ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"
    />

    <Override
        PartName="/xl/styles.xml"
        ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"
    />

</Types>';


/*
|--------------------------------------------------------------------------
| ROOT RELATIONSHIPS
|--------------------------------------------------------------------------
*/

$rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$rootRels .= '
<Relationships
    xmlns="http://schemas.openxmlformats.org/package/2006/relationships"
>

    <Relationship
        Id="rId1"
        Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"
        Target="xl/workbook.xml"
    />

</Relationships>';


/*
|--------------------------------------------------------------------------
| WORKBOOK
|--------------------------------------------------------------------------
*/

$workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$workbook .= '
<workbook
    xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
    xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
>

    <sheets>

        <sheet
            name="' . xlsxXml($sheetName) . '"
            sheetId="1"
            r:id="rId1"
        />

    </sheets>

</workbook>';


/*
|--------------------------------------------------------------------------
| WORKBOOK RELATIONSHIPS
|--------------------------------------------------------------------------
*/

$workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$workbookRels .= '
<Relationships
    xmlns="http://schemas.openxmlformats.org/package/2006/relationships"
>

    <Relationship
        Id="rId1"
        Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"
        Target="worksheets/sheet1.xml"
    />

    <Relationship
        Id="rId2"
        Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"
        Target="styles.xml"
    />

</Relationships>';


/*
|--------------------------------------------------------------------------
| STYLES
|--------------------------------------------------------------------------
*/

$styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$styles .= '
<styleSheet
    xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
>

    <fonts count="2">

        <font>

            <sz val="11"/>

            <name val="Aptos"/>

        </font>

        <font>

            <b/>

            <sz val="11"/>

            <name val="Aptos"/>

        </font>

    </fonts>


    <fills count="3">

        <fill>

            <patternFill patternType="none"/>

        </fill>

        <fill>

            <patternFill patternType="gray125"/>

        </fill>

        <fill>

            <patternFill patternType="solid">

                <fgColor rgb="D9EAF7"/>

                <bgColor indexed="64"/>

            </patternFill>

        </fill>

    </fills>


    <borders count="2">

        <border>

            <left/>

            <right/>

            <top/>

            <bottom/>

            <diagonal/>

        </border>

        <border>

            <left style="thin">

                <color rgb="D9D9D9"/>

            </left>

            <right style="thin">

                <color rgb="D9D9D9"/>

            </right>

            <top style="thin">

                <color rgb="D9D9D9"/>

            </top>

            <bottom style="thin">

                <color rgb="D9D9D9"/>

            </bottom>

            <diagonal/>

        </border>

    </borders>


    <cellXfs count="4">

        <!-- Default -->

        <xf
            numFmtId="0"
            fontId="0"
            fillId="0"
            borderId="0"
        />


        <!-- Header -->

        <xf
            numFmtId="0"
            fontId="1"
            fillId="2"
            borderId="1"
            applyAlignment="1"
        >

            <alignment
                horizontal="center"
                vertical="center"
                wrapText="1"
            />

        </xf>


        <!-- Normal -->

        <xf
            numFmtId="0"
            fontId="0"
            fillId="0"
            borderId="1"
            applyAlignment="1"
        >

            <alignment
                vertical="top"
                wrapText="1"
            />

        </xf>


        <!-- Text -->

        <xf
            numFmtId="49"
            fontId="0"
            fillId="0"
            borderId="1"
            applyAlignment="1"
        >

            <alignment
                vertical="top"
                wrapText="1"
            />

        </xf>

    </cellXfs>

</styleSheet>';


/*
|--------------------------------------------------------------------------
| WORKSHEET
|--------------------------------------------------------------------------
*/

$sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$sheetXml .= '
<worksheet
    xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
>

    <sheetViews>

        <sheetView
            workbookViewId="0"
        >

            <pane
                ySplit="1"
                topLeftCell="A2"
                activePane="bottomLeft"
                state="frozen"
            />

            <selection
                pane="bottomLeft"
                activeCell="A2"
                sqref="A2"
            />

        </sheetView>

    </sheetViews>


    <sheetFormatPr
        defaultRowHeight="18"
    />


    <cols>';


/*
|--------------------------------------------------------------------------
| COLUMN WIDTHS
|--------------------------------------------------------------------------
*/

$columnNumber = 1;


foreach ($exportColumns as $column) {

    $width =
        getColumnWidth(
            $column['Field']
        );


    $sheetXml .= '
        <col
            min="' . $columnNumber . '"
            max="' . $columnNumber . '"
            width="' . $width . '"
            customWidth="1"
        />';


    $columnNumber++;

}


$sheetXml .= '
    </cols>

    <sheetData>';


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

$sheetXml .= '
        <row
            r="1"
            ht="30"
            customHeight="1"
        >';


$columnNumber = 1;


foreach ($exportColumns as $column) {

    $field =
        $column['Field'];


    $header =
        ucwords(
            str_replace(
                '_',
                ' ',
                $field
            )
        );


    $reference =
        cellReference(
            $columnNumber,
            1
        );


    $sheetXml .= '
            <c
                r="' . $reference . '"
                s="1"
                t="inlineStr"
            >

                <is>

                    <t>' .
                        xlsxXml($header) .
                    '</t>

                </is>

            </c>';


    $columnNumber++;

}


$sheetXml .= '
        </row>';


/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/

$rowNumber = 2;


foreach ($records as $record) {

    $sheetXml .= '
        <row
            r="' . $rowNumber . '"
            ht="36"
            customHeight="1"
        >';


    $columnNumber = 1;


    foreach ($exportColumns as $column) {

        $field =
            $column['Field'];

        $fieldLower =
            strtolower($field);

        $value =
            $record[$field] ?? '';


        /*
        |--------------------------------------------------------------------------
        | EMPTY VALUES
        |--------------------------------------------------------------------------
        */

        if (
            $value === null ||
            trim((string)$value) === ''
        ) {

            $value = 'NA';

        }


        /*
        |--------------------------------------------------------------------------
        | LRN
        |--------------------------------------------------------------------------
        */

        if ($fieldLower === 'lrn') {

            /*
             * Keep ONLY the digits.
             *
             * It is stored as a string in XLSX.
             *
             * This prevents scientific notation.
             */

            $value =
                preg_replace(
                    '/[^0-9]/',
                    '',
                    (string)$value
                );


            $style = 3;

        }


        /*
        |--------------------------------------------------------------------------
        | CONTACT NUMBER
        |--------------------------------------------------------------------------
        */

        elseif (
            $fieldLower === 'contact_number' ||
            $fieldLower === 'contact' ||
            $fieldLower === 'phone' ||
            $fieldLower === 'phone_number' ||
            $fieldLower === 'emergency_contact' ||
            $fieldLower === 'emergency_number'
        ) {

            $value =
                formatPhilippineNumber(
                    $value
                );


            $style = 3;

        }


        /*
        |--------------------------------------------------------------------------
        | NORMAL DATA
        |--------------------------------------------------------------------------
        */

        else {

            $style = 2;

        }


        $reference =
            cellReference(
                $columnNumber,
                $rowNumber
            );


        $sheetXml .= '
            <c
                r="' . $reference . '"
                s="' . $style . '"
                t="inlineStr"
            >

                <is>

                    <t xml:space="preserve">' .
                        xlsxXml($value) .
                    '</t>

                </is>

            </c>';


        $columnNumber++;

    }


    $sheetXml .= '
        </row>';


    $rowNumber++;

}


$sheetXml .= '
    </sheetData>';


/*
|--------------------------------------------------------------------------
| AUTO FILTER
|--------------------------------------------------------------------------
*/

$lastColumnLetter =
    excelColumn(
        count($exportColumns)
    );


$lastRow =
    count($records) + 1;


$sheetXml .= '
    <autoFilter
        ref="A1:' .
        $lastColumnLetter .
        $lastRow .
    '"
    />';


/*
|--------------------------------------------------------------------------
| PRINT SETTINGS
|--------------------------------------------------------------------------
*/

$sheetXml .= '

    <pageMargins
        left="0.25"
        right="0.25"
        top="0.5"
        bottom="0.5"
        header="0"
        footer="0"
    />

</worksheet>';


/*
|--------------------------------------------------------------------------
| BUILD XLSX FILE
|--------------------------------------------------------------------------
*/

$files = [

    '[Content_Types].xml' =>
        $contentTypes,

    '_rels/.rels' =>
        $rootRels,

    'xl/workbook.xml' =>
        $workbook,

    'xl/_rels/workbook.xml.rels' =>
        $workbookRels,

    'xl/styles.xml' =>
        $styles,

    'xl/worksheets/sheet1.xml' =>
        $sheetXml

];


$xlsxData =
    createZipFile($files);


/*
|--------------------------------------------------------------------------
| DOWNLOAD REAL XLSX
|--------------------------------------------------------------------------
*/

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header(
    'Content-Length: ' .
    strlen($xlsxData)
);

header('Cache-Control: max-age=0');

header('Pragma: public');


echo $xlsxData;

exit;