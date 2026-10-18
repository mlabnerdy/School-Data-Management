(function(){
  const defaults={primary:'#0d6efd',button:'#0d6efd',header:'#ffffff',card:'#ffffff',background:'#f5f8fc',text:'#172b4d',border:'#e5eaf0'};
  const presets={
    default:{...defaults},
    ocean:{primary:'#087e8b',button:'#087e8b',header:'#ffffff',card:'#ffffff',background:'#edfafa',text:'#153b43',border:'#d3e9e9'},
    forest:{primary:'#28734b',button:'#28734b',header:'#ffffff',card:'#ffffff',background:'#f0f7f1',text:'#1e3929',border:'#d9e8dc'},
    royal:{primary:'#6246a5',button:'#6246a5',header:'#ffffff',card:'#ffffff',background:'#f5f2fc',text:'#2d2345',border:'#e4dcf4'},
    warm:{primary:'#b45309',button:'#b45309',header:'#ffffff',card:'#ffffff',background:'#fff8ed',text:'#422f1d',border:'#f0e2cd'},
    dark:{primary:'#60a5fa',button:'#2563eb',header:'#1e293b',card:'#1e293b',background:'#0f172a',text:'#e5edf8',border:'#334155'}
  };
  function validColor(v){return /^#[0-9a-f]{6}$/i.test(v||'');}
  function apply(theme){const t={...defaults,...theme};const root=document.documentElement;Object.keys(defaults).forEach(k=>{if(validColor(t[k]))root.style.setProperty('--theme-'+(k==='background'?'background':k),t[k]);});root.style.setProperty('--theme-primary-hover',t.primary);root.style.setProperty('--purple',t.button);root.style.setProperty('--accent',t.primary);root.style.setProperty('--panel',t.card);root.style.setProperty('--navy',t.background);root.style.setProperty('--text',t.text);root.style.setProperty('--line',t.border);}
  function get(){try{return {...defaults,...JSON.parse(localStorage.getItem('sdmsTheme')||'{}')};}catch(e){return {...defaults};}}
  window.SDMSTheme={defaults,presets,apply,get,save:function(t){localStorage.setItem('sdmsTheme',JSON.stringify(t));apply(t);}};
  apply(get());
})();
