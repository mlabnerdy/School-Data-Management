<?php
require_once __DIR__ . '/bulletin_helpers.php';
require_login();

$pageTitle = 'Calendar of Events';
$pageStylesheets = ['assets/css/calendar.css'];
$isAdmin = bulletin_is_admin();
$errors = [];
$flash = bulletin_get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    bulletin_require_admin();
    bulletin_verify_csrf();
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'create') {
        $title = trim((string)($_POST['title'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $date = trim((string)($_POST['event_date'] ?? ''));
        $start = trim((string)($_POST['start_time'] ?? ''));
        $end = trim((string)($_POST['end_time'] ?? ''));
        $location = trim((string)($_POST['location'] ?? ''));
        $category = (string)($_POST['category'] ?? 'Event');
        $allowed = ['Notice','Event','Academics','Community'];
        $validDate = DateTime::createFromFormat('Y-m-d', $date);
        if ($title === '' || mb_strlen($title) > 200) $errors[] = 'Enter an event title (up to 200 characters).';
        if (!$validDate || $validDate->format('Y-m-d') !== $date) $errors[] = 'Choose a valid event date.';
        if (!in_array($category, $allowed, true)) $errors[] = 'Choose a valid category.';
        if ($start !== '' && $end !== '' && $end < $start) $errors[] = 'End time must be after start time.';
        if (!$errors) {
            $stmt = $pdo->prepare("INSERT INTO calendar_events (title, description, event_date, start_time, end_time, location, category, created_by) VALUES (:title, :description, :event_date, :start_time, :end_time, :location, :category, :created_by)");
            $stmt->execute([
                ':title'=>$title, ':description'=>$description ?: null, ':event_date'=>$date,
                ':start_time'=>$start ?: null, ':end_time'=>$end ?: null, ':location'=>$location ?: null,
                ':category'=>$category, ':created_by'=>(int)($_SESSION['user_id'] ?? 0)
            ]);
            bulletin_flash('success', 'Calendar event added.');
            redirect('calendar.php');
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['event_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM calendar_events WHERE id = :id');
            $stmt->execute([':id'=>$id]);
            bulletin_flash('success', 'Calendar event deleted.');
            redirect('calendar.php');
        }
    }
}
$monthInput = (string)($_GET['month'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $monthInput) || !checkdate((int)substr($monthInput,5,2), 1, (int)substr($monthInput,0,4))) $monthInput = date('Y-m');
$monthStart = new DateTimeImmutable($monthInput . '-01');
$monthEnd = $monthStart->modify('last day of this month');
$prev = $monthStart->modify('-1 month')->format('Y-m');
$next = $monthStart->modify('+1 month')->format('Y-m');
$stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE event_date BETWEEN :start AND :end ORDER BY event_date, start_time, title");
$stmt->execute([':start'=>$monthStart->format('Y-m-d'), ':end'=>$monthEnd->format('Y-m-d')]);
$events = $stmt->fetchAll();
$byDate = [];
foreach ($events as $event) $byDate[$event['event_date']][] = $event;
$calendarStart = $monthStart->modify('Sunday this week');
if ((int)$monthStart->format('w') === 0) $calendarStart = $monthStart;
$calendarEnd = $monthEnd->modify('Saturday this week');
if ((int)$monthEnd->format('w') === 6) $calendarEnd = $monthEnd;
require_once __DIR__ . '/header.php';
?>
<section class="calendar-shell">
  <div class="calendar-heading">
    <div><p class="calendar-eyebrow">School Data Management System</p><h1>Calendar of Events</h1><p class="calendar-subtitle">School activities, notices, and important dates in one place.</p></div>
    <div class="calendar-actions">
      <a class="calendar-btn calendar-btn-light" href="bulletin_board.php"><i class="bi bi-megaphone"></i> Bulletin Board</a>
      <?php if ($isAdmin): ?><button class="calendar-btn calendar-btn-primary" type="button" onclick="document.getElementById('event-form').scrollIntoView({behavior:'smooth'})"><i class="bi bi-plus-lg"></i> Add event</button><?php endif; ?>
    </div>
  </div>
  <?php if ($flash): ?><div class="calendar-alert success"><?= e($flash['message']) ?></div><?php endif; ?>
  <?php foreach ($errors as $error): ?><div class="calendar-alert error"><?= e($error) ?></div><?php endforeach; ?>
  <div class="calendar-layout">
    <section class="calendar-panel">
      <div class="month-toolbar">
        <div class="month-navigation"><a class="round-arrow" href="calendar.php?month=<?= e($prev) ?>" aria-label="Previous month"><i class="bi bi-chevron-left"></i></a><h2><?= e($monthStart->format('F Y')) ?></h2><a class="round-arrow" href="calendar.php?month=<?= e($next) ?>" aria-label="Next month"><i class="bi bi-chevron-right"></i></a></div>
        <div class="calendar-legend"><span><i class="legend-dot notice"></i>Notice</span><span><i class="legend-dot event"></i>Event</span><span><i class="legend-dot academics"></i>Academics</span><span><i class="legend-dot community"></i>Community</span></div>
      </div>
      <div class="calendar-grid">
        <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day): ?><div class="weekday"><?= $day ?></div><?php endforeach; ?>
        <?php
        $gridStart = $monthStart->modify('-' . $monthStart->format('w') . ' days');
        $gridEnd = $monthEnd->modify('+' . (6 - (int)$monthEnd->format('w')) . ' days');
        for ($d=$gridStart; $d <= $gridEnd; $d=$d->modify('+1 day')):
          $key=$d->format('Y-m-d'); $inMonth=$d->format('m')===$monthStart->format('m'); $today=$key===date('Y-m-d');
        ?>
          <div class="calendar-day <?= $inMonth?'':'outside-month' ?> <?= $today?'today':'' ?>">
            <span class="day-number"><?= $d->format('j') ?></span>
            <?php foreach (array_slice($byDate[$key] ?? [],0,2) as $ev): ?><a class="event-chip <?= strtolower(e($ev['category'])) ?>" href="#event-<?= (int)$ev['id'] ?>" title="<?= e($ev['title']) ?>"><?= e($ev['title']) ?></a><?php endforeach; ?>
            <?php if (count($byDate[$key] ?? [])>2): ?><span class="more-events">+<?= count($byDate[$key])-2 ?> more</span><?php endif; ?>
          </div>
        <?php endfor; ?>
      </div>
    </section>
    <aside class="events-panel">
      <div class="selected-event"><span class="selected-label"><?= e($monthStart->format('F Y')) ?></span><h2>School events</h2><p>All scheduled events for this month.</p></div>
      <h3 class="upcoming-title">Events this month</h3>
      <?php if (!$events): ?><div class="calendar-empty"><i class="bi bi-calendar2-week"></i><strong>No events scheduled</strong><span>New school events will appear here.</span></div><?php endif; ?>
      <?php foreach ($events as $ev): ?>
      <article class="event-row" id="event-<?= (int)$ev['id'] ?>">
        <div class="event-date <?= strtolower(e($ev['category'])) ?>"><span><?= e(date('M',strtotime($ev['event_date']))) ?></span><strong><?= e(date('d',strtotime($ev['event_date']))) ?></strong></div>
        <div class="event-details"><h4><?= e($ev['title']) ?></h4><p><?= e($ev['start_time'] ? date('g:i A',strtotime($ev['start_time'])) : 'All day') ?><?= $ev['end_time'] ? ' – '.e(date('g:i A',strtotime($ev['end_time']))) : '' ?><?= $ev['location'] ? ' · '.e($ev['location']) : '' ?></p><?php if ($ev['description']): ?><p class="event-description"><?= e($ev['description']) ?></p><?php endif; ?>
        <?php if ($isAdmin): ?><form method="post" class="delete-event-form" onsubmit="return confirm('Delete this calendar event?')"><input type="hidden" name="csrf_token" value="<?= e(bulletin_csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="event_id" value="<?= (int)$ev['id'] ?>"><button type="submit" class="delete-event">Delete</button></form><?php endif; ?></div>
      </article>
      <?php endforeach; ?>
    </aside>
  </div>
  <?php if ($isAdmin): ?>
  <section class="event-form-panel" id="event-form"><div><p class="calendar-eyebrow">Administrator tools</p><h2>Add a calendar event</h2><p class="calendar-subtitle">Events added here appear in the calendar for all users.</p></div>
  <form method="post" class="event-form"><input type="hidden" name="csrf_token" value="<?= e(bulletin_csrf_token()) ?>"><input type="hidden" name="action" value="create">
    <label>Event title<input name="title" maxlength="200" required value="<?= e($_POST['title'] ?? '') ?>"></label>
    <label>Date<input type="date" name="event_date" required value="<?= e($_POST['event_date'] ?? date('Y-m-d')) ?>"></label>
    <label>Category<select name="category"><option>Notice</option><option selected>Event</option><option>Academics</option><option>Community</option></select></label>
    <label>Start time<input type="time" name="start_time"></label><label>End time<input type="time" name="end_time"></label>
    <label>Location<input name="location" maxlength="200" placeholder="e.g. School hall"></label>
    <label class="wide-field">Description<textarea name="description" rows="3" placeholder="Optional event details"></textarea></label>
    <div class="wide-field"><button class="calendar-btn calendar-btn-primary" type="submit"><i class="bi bi-plus-lg"></i> Save event</button></div>
  </form></section>
  <?php endif; ?>
</section>
<?php require_once __DIR__ . '/footer.php'; ?>
