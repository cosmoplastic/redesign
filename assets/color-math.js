/* ══════════════════════════════════════════════════
   ONE DESIGN — SHARED COLOR MATH
   color-math.js
══════════════════════════════════════════════════ */

function lin(c){ c/=255; return c<=.04045 ? c/12.92 : Math.pow((c+.055)/1.055,2.4); }
function delin(c){ if(c<=0)return 0; if(c>=1)return 255; return Math.round((c<=.0031308?12.92*c:1.055*Math.pow(c,1/2.4)-.055)*255); }

function rgbToOklch(r,g,b){
  const rl=lin(r),gl=lin(g),bl=lin(b);
  const l=.4122214708*rl+.5363325363*gl+.0514459929*bl;
  const m=.2119034982*rl+.6806995451*gl+.1073969566*bl;
  const s=.0883024619*rl+.2817188376*gl+.6299787005*bl;
  const l_=Math.cbrt(l),m_=Math.cbrt(m),s_=Math.cbrt(s);
  const L=.2104542553*l_+.7936177850*m_-.0040720468*s_;
  const a=1.9779984951*l_-2.4285922050*m_+.4505937099*s_;
  const bv=.0259040371*l_+.7827717662*m_-.8086757660*s_;
  return [L, Math.sqrt(a*a+bv*bv), (Math.atan2(bv,a)*180/Math.PI+360)%360];
}

function oklchToRgb(L,C,H){
  const a=C*Math.cos(H*Math.PI/180), b=C*Math.sin(H*Math.PI/180);
  const l_=L+.3963377774*a+.2158037573*b;
  const m_=L-.1055613458*a-.0638541728*b;
  const s_=L-.0894841775*a-1.2914855480*b;
  const lc=l_**3, mc=m_**3, sc=s_**3;
  return [
    delin(4.0767416621*lc-3.3077115913*mc+.2309699292*sc),
    delin(-1.2684380046*lc+2.6097574011*mc-.3413193965*sc),
    delin(-.0041960863*lc-.7034186147*mc+1.7076147010*sc)
  ];
}

function hexToRgb(hex){
  hex=hex.replace('#','');
  if(hex.length===3) hex=hex.split('').map(c=>c+c).join('');
  if(hex.length!==6) return null;
  const n=parseInt(hex,16);
  if(isNaN(n)) return null;
  return [n>>16&255, n>>8&255, n&255];
}

function rgbToHex(r,g,b){
  return '#'+[r,g,b].map(v=>Math.max(0,Math.min(255,Math.round(v))).toString(16).padStart(2,'0')).join('');
}

function oklchToHex(L,C,H){ return rgbToHex(...oklchToRgb(L,C,H)); }

function clamp(v,a,b){ return Math.max(a,Math.min(b,v)); }

function textColorFor(hex){
  const [r,g,b]=hexToRgb(hex)||[0,0,0];
  return .2126*(r/255)+.7152*(g/255)+.0722*(b/255) > .45 ? '#1a1a18' : '#f5f2ee';
}

function isInGamut(L,C,H){
  const [r,g,b]=oklchToRgb(L,C,H);
  const [L2,C2]=rgbToOklch(r,g,b);
  return Math.abs(C-C2)<.01 && Math.abs(L-L2)<.02;
}

function clampToGamut(L,C,H){
  if(isInGamut(L,C,H)) return [L,C,H];
  let lo=0, hi=C;
  for(let i=0;i<20;i++){
    const mid=(lo+hi)/2;
    if(isInGamut(L,mid,H)) lo=mid; else hi=mid;
  }
  return [L,(lo+hi)/2,H];
}

/* palette scale generator */
const SCALE_LIGHTNESS = {25:.990,50:.97,75:.955,100:.93,200:.87,300:.80,400:.70,500:.60,600:.50,700:.40,800:.30,900:.20,950:.13,975:.09};
const SCALE_CHROMA    = {25:.015,50:.04,75:.055,100:.07,200:.11,300:.15,400:.19,500:1.00,600:.95,700:.85,800:.70,900:.55,950:.42,975:.30};
const SCALE_STOPS     = [50,100,200,300,400,500,600,700,800,900];
/* All 14 stops in priority order — first 10 = standard Tailwind-style scale */
const ALL_STOPS       = [50,100,200,300,400,500,600,700,800,900,950,25,75,975];

function genScaleWithStops(hex, stops){
  const [r,g,b]=hexToRgb(hex);
  const [L,C,H]=rgbToOklch(r,g,b);
  return stops.map(stop=>{
    const tC=stop===500?C:C*SCALE_CHROMA[stop];
    return rgbToHex(...oklchToRgb(SCALE_LIGHTNESS[stop],clamp(tC,0,.38),H));
  });
}

function genScale(hex){ return genScaleWithStops(hex, SCALE_STOPS); }

/* toast helper */
function showToast(msg){
  let t=document.getElementById('toast');
  if(!t){ t=document.createElement('div'); t.id='toast'; t.className='toast'; document.body.appendChild(t); }
  t.textContent=msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer=setTimeout(()=>t.classList.remove('show'),1800);
}

function copyText(text, msg){
  navigator.clipboard.writeText(text);
  showToast(msg||'Copied!');
}
