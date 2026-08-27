<?php
require __DIR__ . '/inc/functions.php';
$BASE    = base_url();
$infos   = load_json('infos.json', []);
$profs   = load_json('professeurs.json', []);
$galerie = load_json('galerie.json', []);
$actus   = load_json('actualites.json', []);
$aikido    = load_json('aikido.json', []);
$histoire  = load_json('histoire.json', []);
$faq       = load_json('faq.json', []);
$memoriam  = load_json('memoriam.json', []);
$stagesAll = load_json('stages.json', []);
$stages    = array_values(array_filter($stagesAll, fn($s) => ($s['date'] ?? '') >= date('Y-m-d')));
usort($stages, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
$afa       = afa_events(8);
$nextCourse = next_course($infos);
$glossaire = load_json('glossaire.json', []);
$biblio    = load_json('bibliographie.json', []);
$contact   = load_json('contact.json', []);
$techniques= load_json('techniques.json', []);
$armes     = load_json('armes.json', []);
$liens     = load_json('liens.json', []);
$lib       = load_json('libelles.json', []);
$alertOn = settings('alert_enabled') && trim((string) settings('alert_text')) !== '';
$themeOr = (($_GET['theme'] ?? '') === 'or');
$logoImg = $themeOr ? 'images/logo-or.png' : 'images/logo.png';
?>
<!DOCTYPE html>
<html lang="fr"<?php echo $themeOr ? ' class="theme-or"' : ''; ?>>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>Musubi Dojo Jurbise · École d'Aïkido</title>
<meta name="description" content="École d'Aïkido Musubi Dojo à Jurbise. Aïkido traditionnel Aïkikaï pour adultes et enfants. Deux cours d'essai gratuits. Académie de Police de Jurbise, route d'Ath 25-35, 7050 Jurbise."/>
<meta name="keywords" content="Aïkido, Jurbise, Musubi Dojo, art martial, Aïkikaï, AFA, Mons, Nimy, cours enfants, self-défense"/>
<meta property="og:title" content="Musubi Dojo Jurbise · École d'Aïkido"/>
<meta property="og:description" content="Aïkido traditionnel à Jurbise depuis 1987. Deux cours d'essai gratuits."/>
<meta property="og:type" content="website"/>
<meta property="og:url" content="<?php echo $BASE; ?>/"/>
<meta property="og:image" content="<?php echo $BASE; ?>/images/og.png"/>
<meta property="og:image:width" content="1200"/>
<meta property="og:image:height" content="630"/>
<meta property="og:locale" content="fr_BE"/>
<meta property="og:site_name" content="Musubi Dojo Jurbise"/>
<meta name="twitter:card" content="summary_large_image"/>
<meta name="twitter:title" content="Musubi Dojo Jurbise · École d'Aïkido"/>
<meta name="twitter:description" content="Aïkido traditionnel à Jurbise depuis 1987. Deux cours d'essai gratuits."/>
<meta name="twitter:image" content="<?php echo $BASE; ?>/images/og.png"/>
<meta name="theme-color" content="#17130d"/>
<link rel="canonical" href="<?php echo $BASE; ?>/"/>
<link rel="icon" href="images/favicon.ico" sizes="any"/>
<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32.png"/>
<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16.png"/>
<link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png"/>
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"SportsClub",
  "name":"École d'Aïkido Musubi Dojo Jurbise",
  "alternateName":"Musubi Dojo",
  "url":"<?php echo $BASE; ?>/",
  "logo":"<?php echo $BASE; ?>/images/logo.png",
  "image":"<?php echo $BASE; ?>/images/og.png",
  "description":"École d'Aïkido traditionnel Aïkikaï à Jurbise, pour adultes et enfants, depuis 1987.",
  "sport":"Aïkido",
  "foundingDate":"1987",
  "email":"benoit.toulotte@aikido.be",
  "telephone":"+32476565257",
  "priceRange":"€€",
  "address":{"@type":"PostalAddress","streetAddress":"Route d'Ath 25-35","addressLocality":"Jurbise","postalCode":"7050","addressCountry":"BE"},
  "geo":{"@type":"GeoCoordinates","latitude":50.50327,"longitude":3.93139},
  "sameAs":["https://www.facebook.com/MusubiDojoJurbise/"],
  "openingHoursSpecification":[
    {"@type":"OpeningHoursSpecification","dayOfWeek":["Monday","Wednesday"],"opens":"20:00","closes":"22:00"},
    {"@type":"OpeningHoursSpecification","dayOfWeek":"Sunday","opens":"10:30","closes":"12:30"},
    {"@type":"OpeningHoursSpecification","dayOfWeek":"Sunday","opens":"09:30","closes":"10:30"}
  ]
}
</script>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Noto+Serif+JP:wght@500;700&display=swap" onload="this.onload=null;this.rel='stylesheet'"/>
<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Noto+Serif+JP:wght@500;700&display=swap"/></noscript>
<style>
:root{
  --ink:#17130d;
  --sumi:#211b13;
  --paper:#f7f1e6;
  --paper-2:#efe6d5;
  --paper-3:#e7dcc6;
  --vermillion:#c33025;
  --vermillion-d:#9c231b;
  --gold:#b08d57;
  --indigo:#1e2b40;
  --muted:#6f6552;
  --line:rgba(23,19,13,.12);
  --shadow:0 18px 50px -20px rgba(23,19,13,.45);
  --radius:18px;
  --maxw:1160px;
}
*{box-sizing:border-box}
html{scroll-behavior:smooth;scroll-padding-top:76px;overflow-x:hidden}
body{
  margin:0;
  font-family:'Inter',system-ui,sans-serif;
  color:var(--ink);
  background:var(--paper);
  line-height:1.7;
  -webkit-font-smoothing:antialiased;
  overflow-x:hidden;
}
h1,h2,h3,.serif{font-family:'Cormorant Garamond',Georgia,serif;font-weight:600;line-height:1.12;letter-spacing:.2px}
img{max-width:100%;display:block}
a{color:inherit;text-decoration:none}
.jp{font-family:'Noto Serif JP',serif}

/* ---------- utilities ---------- */
.wrap{max-width:var(--maxw);margin:0 auto;padding:0 24px}
.section{padding:104px 0}
.eyebrow{
  display:inline-flex;align-items:center;gap:10px;
  font-size:.72rem;letter-spacing:.32em;text-transform:uppercase;
  color:var(--vermillion);font-weight:600;margin:0 0 14px;
}
.eyebrow::before{content:"";width:30px;height:1px;background:var(--vermillion);opacity:.6}
.h-sec{font-size:clamp(2.1rem,4.6vw,3.4rem);margin:0 0 20px}
.lead{font-size:1.12rem;color:var(--muted);max-width:60ch}
.btn{
  display:inline-flex;align-items:center;gap:10px;
  padding:14px 26px;border-radius:999px;font-weight:600;font-size:.95rem;
  transition:.28s ease;cursor:pointer;border:1.5px solid transparent;
}
.btn-primary{background:var(--vermillion);color:#fff;box-shadow:0 14px 30px -12px rgba(195,48,37,.7)}
.btn-primary:hover{background:var(--vermillion-d);transform:translateY(-3px)}
.btn-ghost{border-color:rgba(255,255,255,.35);color:#fff}
.btn-ghost:hover{background:rgba(255,255,255,.12);transform:translateY(-3px)}
.btn-dark{border-color:var(--line);color:var(--ink)}
.btn-dark:hover{background:var(--ink);color:var(--paper);transform:translateY(-3px)}

/* reveal */
.reveal{opacity:0;transform:translateY(28px);transition:opacity .8s ease,transform .8s ease}
.reveal.in{opacity:1;transform:none}
@media (prefers-reduced-motion:reduce){.reveal{opacity:1;transform:none;transition:none}html{scroll-behavior:auto}}
.no-js .reveal{opacity:1;transform:none}
.no-js .faq-a{max-height:none}

/* seigaiha wave motif as reusable bg */
.waves{
  background-image:radial-gradient(circle at 10px 18px, transparent 8px, rgba(23,19,13,.05) 9px, transparent 10px);
  background-size:20px 20px;
}

/* ---------- header ---------- */
header{
  position:fixed;top:0;left:0;right:0;z-index:60;
  transition:.35s ease;
}
.nav{
  display:flex;align-items:center;justify-content:space-between;
  padding:16px 24px;max-width:var(--maxw);margin:0 auto;
}
header.scrolled{background:rgba(247,241,230,.92);backdrop-filter:blur(12px);box-shadow:0 1px 0 var(--line)}
.brand{display:flex;align-items:center;gap:12px;color:#fff;transition:.35s}
header.scrolled .brand{color:var(--ink)}
.brand img{width:44px;height:44px;border-radius:50%;box-shadow:0 6px 16px -6px rgba(0,0,0,.5)}
.brand b{font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:700;letter-spacing:.5px;line-height:1}
.brand span{display:block;font-family:'Inter';font-size:.62rem;letter-spacing:.34em;text-transform:uppercase;opacity:.8;font-weight:500}
.menu{display:flex;align-items:center;gap:6px}
.menu a{
  padding:9px 14px;border-radius:999px;font-size:.86rem;font-weight:500;color:rgba(255,255,255,.9);transition:.25s
}
header.scrolled .menu a{color:var(--ink)}
.menu a:hover{background:rgba(255,255,255,.15)}
header.scrolled .menu a:hover{background:var(--paper-3)}
.menu a.cta{background:var(--vermillion);color:#fff!important}
.menu a.cta:hover{background:var(--vermillion-d)}
.burger{display:none;flex-direction:column;gap:5px;background:none;border:0;cursor:pointer;padding:8px}
.burger span{width:26px;height:2px;background:#fff;transition:.3s}
header.scrolled .burger span{background:var(--ink)}

/* ---------- hero ---------- */
.hero{
  position:relative;min-height:100vh;display:flex;align-items:center;
  color:#f7f1e6;overflow:hidden;
  background:
    radial-gradient(120% 120% at 80% 0%, #33261a 0%, transparent 55%),
    linear-gradient(160deg,#1b1611 0%,#17130d 60%,#0f0c08 100%);
}
.hero-waves{
  position:absolute;inset:0;opacity:.5;pointer-events:none;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='60' viewBox='0 0 120 60'%3E%3Cg fill='none' stroke='%23b08d57' stroke-opacity='0.16' stroke-width='1.4'%3E%3Cpath d='M0 60c15 0 15-24 30-24s15 24 30 24 15-24 30-24 15 24 30 24'/%3E%3Cpath d='M0 44c15 0 15-24 30-24s15 24 30 24 15-24 30-24 15 24 30 24'/%3E%3Cpath d='M0 28c15 0 15-24 30-24s15 24 30 24 15-24 30-24 15 24 30 24'/%3E%3C/g%3E%3C/svg%3E");
  background-size:180px 90px;
  -webkit-mask-image:linear-gradient(to top,transparent,#000 60%);
  mask-image:linear-gradient(to top,transparent,#000 60%);
}
.hero-kanji{
  position:absolute;right:-4vw;top:44%;transform:translateY(-50%);
  font-family:'Noto Serif JP',serif;font-weight:700;
  font-size:min(58vw,760px);line-height:.8;color:rgba(195,48,37,.10);
  user-select:none;pointer-events:none;
}
.hero-inner{position:relative;z-index:2;max-width:var(--maxw);margin:0 auto;padding:120px 24px 90px;width:100%}
.hero .tag{
  display:inline-flex;align-items:center;gap:10px;
  font-size:.72rem;letter-spacing:.34em;text-transform:uppercase;
  color:var(--gold);font-weight:600;margin-bottom:26px;
}
.hero .tag::before,.hero .tag::after{content:"";width:26px;height:1px;background:var(--gold);opacity:.6}
.hero h1{font-size:clamp(3rem,8vw,6.4rem);font-weight:700;margin:0;letter-spacing:.5px}
.hero h1 em{font-style:normal;color:var(--vermillion);display:inline-block}
.hero .sub{font-size:clamp(1.05rem,2vw,1.35rem);color:rgba(247,241,230,.82);max-width:52ch;margin:22px 0 8px;font-weight:300}
.hero .place{margin-top:10px;font-size:.95rem;color:rgba(247,241,230,.6);letter-spacing:.02em}
.hero-cta{display:flex;flex-wrap:wrap;gap:14px;margin-top:38px}
.scrolldown{
  position:absolute;bottom:26px;left:50%;transform:translateX(-50%);z-index:2;
  color:rgba(247,241,230,.6);font-size:.7rem;letter-spacing:.3em;text-transform:uppercase;
  display:flex;flex-direction:column;align-items:center;gap:10px;
}
.scrolldown i{width:1px;height:42px;background:linear-gradient(var(--gold),transparent);animation:pulse 2.2s infinite}
@keyframes pulse{0%,100%{opacity:.3}50%{opacity:1}}

/* ---------- stats strip ---------- */
.strip{background:var(--ink);color:var(--paper)}
.strip .wrap{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;padding:44px 24px}
.stat{text-align:center}
.stat b{display:block;font-family:'Cormorant Garamond',serif;font-size:2.8rem;font-weight:700;color:var(--gold);line-height:1}
.stat span{font-size:.78rem;letter-spacing:.14em;text-transform:uppercase;color:rgba(247,241,230,.6)}

/* ---------- aikido ---------- */
.split{display:grid;grid-template-columns:1.05fr .95fr;gap:70px;align-items:center}
.enso{position:relative;aspect-ratio:1;max-width:420px;margin:0 auto;width:100%}
.enso svg{width:100%;height:100%;filter:drop-shadow(0 20px 40px rgba(195,48,37,.18))}
.enso .cn{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center}
.enso .cn .k{font-family:'Noto Serif JP',serif;font-size:3.4rem;color:var(--ink);line-height:1}
.enso .cn small{letter-spacing:.3em;text-transform:uppercase;font-size:.7rem;color:var(--muted);margin-top:8px}
.values{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin-top:34px}
.value{background:#fff;border:1px solid var(--line);border-radius:14px;padding:20px}
.value b{display:block;font-family:'Cormorant Garamond',serif;font-size:1.35rem;color:var(--vermillion);margin-bottom:4px}
.value p{margin:0;font-size:.92rem;color:var(--muted)}

/* ---------- dojo / horaires / tarifs ---------- */
.dojo{background:var(--paper-2)}
.cards-3{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;margin-top:46px}
.card{
  background:#fff;border:1px solid var(--line);border-radius:var(--radius);
  padding:34px 30px;box-shadow:0 10px 30px -22px rgba(23,19,13,.4);transition:.3s
}
.card:hover{transform:translateY(-6px);box-shadow:var(--shadow)}
.card .ic{width:52px;height:52px;border-radius:14px;display:grid;place-items:center;background:var(--paper-3);margin-bottom:18px}
.card .ic svg{width:26px;height:26px;stroke:var(--vermillion)}
.card h3{font-size:1.5rem;margin:0 0 12px}
.card ul{margin:0;padding:0;list-style:none}
.card li{padding:9px 0;border-bottom:1px dashed var(--line);display:flex;justify-content:space-between;gap:14px;font-size:.95rem}
.card li:last-child{border-bottom:0}
.card li span{color:var(--muted)}
.card li b{color:var(--ink);font-weight:600;text-align:right}
.note{font-size:.85rem;color:var(--vermillion);font-weight:600;margin-top:14px}
.map-row{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:center;margin-top:56px}
.map-row iframe{width:100%;height:340px;border:0;border-radius:var(--radius);box-shadow:var(--shadow);filter:grayscale(.15)}
.addr h3{font-size:1.9rem;margin:0 0 10px}
.addr p{color:var(--muted);margin:6px 0}
.addr .pins{display:flex;flex-direction:column;gap:14px;margin-top:22px}
.addr .pin{display:flex;gap:14px;align-items:flex-start}
.addr .pin svg{width:22px;height:22px;stroke:var(--vermillion);flex:0 0 auto;margin-top:3px}

/* ---------- teachers ---------- */
.teachers{display:grid;grid-template-columns:repeat(3,1fr);gap:28px;margin-top:48px}
.teacher{background:#fff;border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;transition:.3s}
.teacher:hover{transform:translateY(-6px);box-shadow:var(--shadow)}
.teacher .ph{height:300px;overflow:hidden;background:var(--paper-3)}
.teacher .ph img{width:100%;height:100%;object-fit:cover;object-position:top center;filter:grayscale(.2) contrast(1.02);transition:.5s}
.teacher:hover .ph img{filter:none;transform:scale(1.04)}
.teacher .body{padding:22px 24px 26px}
.teacher .role{font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;color:var(--vermillion);font-weight:600}
.teacher h3{font-size:1.6rem;margin:6px 0 6px}
.teacher .grade{font-size:.9rem;color:var(--muted)}
.teacher.wide{grid-column:span 3;display:grid;grid-template-columns:220px 1fr;align-items:stretch}
.teacher.wide .ph{height:auto}
.teacher.wide .body{padding:30px 34px}

/* ---------- timeline ---------- */
.history{background:var(--ink);color:var(--paper);position:relative;overflow:hidden}
.history .hero-kanji{color:rgba(176,141,87,.06);font-size:min(60vw,720px)}
.tl{position:relative;margin-top:56px;padding-left:0}
.tl::before{content:"";position:absolute;left:120px;top:6px;bottom:6px;width:2px;background:linear-gradient(var(--gold),transparent)}
.tl-item{display:grid;grid-template-columns:120px 1fr;gap:38px;padding:0 0 40px;position:relative}
.tl-item .yr{text-align:right;font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;color:var(--gold);position:relative}
.tl-item .yr::after{content:"";position:absolute;right:-27px;top:10px;width:12px;height:12px;border-radius:50%;background:var(--vermillion);box-shadow:0 0 0 4px rgba(195,48,37,.2)}
.tl-item p{margin:6px 0 0;color:rgba(247,241,230,.82);max-width:60ch}
.tl-item p b{color:#fff;font-weight:600}

/* ---------- videos ---------- */
.vid-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:44px}
.vid{
  background:#fff;border:1px solid var(--line);border-radius:14px;padding:20px 22px;
  display:flex;gap:14px;align-items:center;transition:.3s;font-size:.96rem;color:var(--ink);text-decoration:none
}
.vid.link{cursor:pointer}
.vid.link:hover{border-color:var(--vermillion);transform:translateY(-4px);box-shadow:0 14px 30px -20px rgba(195,48,37,.5)}
.vid .n{font-family:'Cormorant Garamond',serif;font-size:1.5rem;font-weight:700;color:var(--vermillion);flex:0 0 auto;width:34px}
.vid b{font-weight:600;display:block}
.vid span{font-size:.8rem;color:var(--muted)}
.vid .vid-go{margin-left:auto;flex:0 0 auto;color:var(--vermillion);font-size:1.2rem;font-weight:700}
.vid>picture{flex:0 0 auto;line-height:0}
.vid-thumb{width:56px;height:56px;border-radius:10px;object-fit:cover;flex:0 0 auto}

/* ---------- news / memoriam ---------- */
.news{background:var(--paper-2)}
.news-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:34px;margin-top:46px;align-items:center}
.newscard{background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:34px;box-shadow:0 10px 30px -24px rgba(23,19,13,.4)}
.newscard .date{font-size:.75rem;letter-spacing:.16em;text-transform:uppercase;color:var(--vermillion);font-weight:600}
.newscard h3{font-size:1.7rem;margin:8px 0 12px}
.newscard p{color:var(--muted);margin:0 0 10px}
.memoriam{background:linear-gradient(160deg,#211b13,#17130d);color:var(--paper);text-align:center;padding:40px 34px;border-radius:var(--radius)}
.memoriam .mimg{width:120px;height:120px;border-radius:50%;object-fit:cover;margin:0 auto 18px;border:3px solid var(--gold);filter:grayscale(.2)}
.memoriam .lbl{font-size:.72rem;letter-spacing:.3em;text-transform:uppercase;color:var(--gold)}
.memoriam h3{font-size:1.8rem;margin:8px 0 4px}
.memoriam .yrs{color:rgba(247,241,230,.6);font-size:.9rem}
.memoriam p{color:rgba(247,241,230,.82);font-size:.92rem;margin-top:14px}
.mem-others{margin-top:18px;font-size:.82rem;color:rgba(247,241,230,.5)}

/* ---------- contact ---------- */
.contact{background:var(--ink);color:var(--paper)}
.contact-grid{display:grid;grid-template-columns:1fr 1fr;gap:60px;margin-top:44px}
.cinfo .row{display:flex;gap:16px;align-items:flex-start;padding:16px 0;border-bottom:1px solid rgba(247,241,230,.12)}
.cinfo .row svg{width:22px;height:22px;stroke:var(--gold);flex:0 0 auto;margin-top:4px}
.cinfo .row b{display:block;font-size:.72rem;letter-spacing:.16em;text-transform:uppercase;color:rgba(247,241,230,.5);margin-bottom:2px;font-weight:600}
.cinfo .row a,.cinfo .row span{color:var(--paper);font-size:1.02rem}
.cinfo .row a:hover{color:var(--gold)}
form{display:grid;gap:16px}
form label{font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;color:rgba(247,241,230,.6);margin-bottom:-8px}
form input,form textarea{
  background:rgba(247,241,230,.06);border:1px solid rgba(247,241,230,.16);border-radius:12px;
  padding:14px 16px;color:var(--paper);font-family:inherit;font-size:.98rem;transition:.25s
}
form input:focus,form textarea:focus{outline:none;border-color:var(--gold);background:rgba(247,241,230,.1)}
form textarea{min-height:120px;resize:vertical}

/* ---------- footer ---------- */
footer{background:#0f0c08;color:rgba(247,241,230,.7)}
.foot{display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:40px;padding:64px 24px 30px;max-width:var(--maxw);margin:0 auto}
.foot .brand{color:var(--paper)}
.foot h3{font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:var(--paper);margin:0 0 16px}
.foot ul{list-style:none;margin:0;padding:0}
.foot li{margin:8px 0;font-size:.92rem}
.foot a:hover{color:var(--gold)}
.foot p{font-size:.9rem;max-width:36ch}
.foot-bottom{border-top:1px solid rgba(247,241,230,.1);text-align:center;padding:22px;font-size:.82rem;color:rgba(247,241,230,.62)}
.foot-bottom a{color:rgba(247,241,230,.75);text-decoration:underline}
.gal-note a{text-decoration:underline}
.foot-bottom a:hover{color:var(--gold)}

.social{display:flex;gap:12px;margin-top:18px}
.social a{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;background:rgba(247,241,230,.08);transition:.25s}
.social a:hover{background:var(--vermillion);transform:translateY(-3px)}
.social svg{width:20px;height:20px;fill:var(--paper)}

/* back to top */
.top{
  position:fixed;right:22px;bottom:22px;z-index:50;
  width:48px;height:48px;border-radius:50%;background:var(--vermillion);color:#fff;
  display:grid;place-items:center;box-shadow:var(--shadow);opacity:0;transform:translateY(20px);
  transition:.35s;pointer-events:none;border:0;cursor:pointer
}
.top.show{opacity:1;transform:none;pointer-events:auto}
.top:hover{background:var(--vermillion-d)}

/* ---------- responsive ---------- */
@media(max-width:960px){
  .split,.map-row,.news-grid,.contact-grid{grid-template-columns:1fr;gap:44px}
  .cards-3,.teachers,.vid-grid{grid-template-columns:1fr 1fr}
  .strip .wrap{grid-template-columns:1fr 1fr;gap:32px}
  .foot{grid-template-columns:1fr 1fr}
  .teacher.wide{grid-template-columns:1fr}
  .tl::before{left:70px}
  .tl-item{grid-template-columns:70px 1fr;gap:24px}
  .tl-item .yr{font-size:1.3rem}
  .tl-item .yr::after{right:-30px}
}
@media(max-width:640px){
  .section{padding:76px 0}
  .menu{
    position:fixed;top:0;right:0;bottom:auto;left:auto;height:100vh;height:100dvh;
    width:min(80vw,320px);background:var(--ink);
    flex-direction:column;align-items:stretch;justify-content:flex-start;gap:6px;
    padding:90px 22px;transform:translateX(100%);transition:.35s;box-shadow:var(--shadow);
    overflow-y:auto;z-index:65
  }
  .menu.open{transform:none}
  .menu a{color:var(--paper)!important;padding:14px 16px;border-radius:12px}
  .burger{display:flex;z-index:70}
  .cards-3,.teachers,.vid-grid,.foot,.strip .wrap{grid-template-columns:1fr}
  .values{grid-template-columns:1fr}
  .hero-cta{flex-direction:column;align-items:stretch}
  .hero-cta .btn{justify-content:center}
}
/* ---------- skip link + focus ---------- */
.skip{position:absolute;left:-999px;top:8px;z-index:100;background:var(--vermillion);color:#fff;padding:10px 16px;border-radius:8px}
.skip:focus{left:8px}
a:focus-visible,button:focus-visible,input:focus-visible,textarea:focus-visible,.vid:focus-visible,.gal-item:focus-visible,.faq-q:focus-visible{outline:3px solid var(--gold);outline-offset:3px;border-radius:6px}

/* ---------- musubi quote ---------- */
.musubi-quote{margin-top:56px;background:linear-gradient(160deg,#241d14,#17130d);border:1px solid rgba(176,141,87,.25);border-radius:var(--radius);padding:46px 48px;position:relative;overflow:hidden}
.musubi-quote .mk{position:absolute;right:24px;top:-30px;font-family:'Noto Serif JP',serif;font-size:200px;color:rgba(195,48,37,.12);line-height:1}
.musubi-quote .lbl{font-size:.72rem;letter-spacing:.3em;text-transform:uppercase;color:var(--gold);position:relative;z-index:2}
.musubi-quote b.term{font-family:'Cormorant Garamond',serif;font-size:2rem;color:var(--paper);display:block;margin:6px 0 4px;position:relative;z-index:2}
.musubi-quote .phon{color:var(--muted);font-style:italic;font-size:.95rem;margin-bottom:16px;position:relative;z-index:2}
.musubi-quote p.q{font-family:'Cormorant Garamond',serif;font-size:1.5rem;line-height:1.5;color:rgba(247,241,230,.92);position:relative;z-index:2;max-width:62ch}
.musubi-quote .src{margin-top:16px;color:var(--gold);font-size:.9rem;letter-spacing:.05em;position:relative;z-index:2}
.musubi-quote .nb{margin-top:8px;color:rgba(247,241,230,.5);font-size:.82rem;position:relative;z-index:2}

/* ---------- memoriam masters ---------- */
.mem-founders{display:flex;justify-content:center;gap:40px;flex-wrap:wrap;margin:4px 0 2px}
.mem-founders figure{margin:0;text-align:center}
.mem-founders .mimg{margin:0 auto 10px}
.mem-founders figcaption b{display:block;font-family:'Cormorant Garamond',serif;font-size:1.3rem;color:#f4ebd7;line-height:1.15}
.mem-founders figcaption span{display:block;font-size:.82rem;color:rgba(247,241,230,.62);margin-top:2px}
.mem-masters{display:flex;justify-content:center;gap:30px;margin-top:22px}
.mem-masters figure{margin:0;text-align:center}
.mem-masters img{width:74px;height:74px;border-radius:50%;object-fit:cover;border:2px solid rgba(176,141,87,.5);filter:grayscale(.3)}
.mem-masters figcaption{font-size:.72rem;color:rgba(247,241,230,.6);margin-top:8px;line-height:1.3}
.mem-masters figcaption b{color:rgba(247,241,230,.85);display:block;font-weight:600}

/* ---------- gallery ---------- */
.gallery-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-top:44px}
.gal-item{border:0;padding:0;cursor:pointer;border-radius:14px;overflow:hidden;aspect-ratio:4/3;background:var(--paper-3);position:relative}
.gal-item img{width:100%;height:100%;object-fit:cover;transition:.5s;filter:grayscale(.15)}
.gal-item:hover img{transform:scale(1.06);filter:none}
.gal-item::after{content:"";position:absolute;inset:0;background:linear-gradient(transparent 60%,rgba(23,19,13,.35));opacity:0;transition:.3s}
.gal-item:hover::after{opacity:1}
.gal-note{text-align:center;color:var(--muted);font-size:.88rem;margin-top:22px}
/* lightbox */
.lb{position:fixed;inset:0;z-index:90;background:rgba(15,12,8,.92);display:none;align-items:center;justify-content:center;padding:30px}
.lb.open{display:flex}
.lb img{max-width:92vw;max-height:86vh;border-radius:8px;box-shadow:0 30px 80px -20px #000}
.lb button{position:absolute;background:rgba(247,241,230,.1);border:0;color:#fff;width:52px;height:52px;border-radius:50%;font-size:1.5rem;cursor:pointer;transition:.25s}
.lb button:hover{background:var(--vermillion)}
.lb .lb-close{top:24px;right:24px}
.lb .lb-prev{left:24px;top:50%;transform:translateY(-50%)}
.lb .lb-next{right:24px;top:50%;transform:translateY(-50%)}

/* ---------- stages ---------- */
.stage-feat{display:grid;grid-template-columns:200px 1fr;gap:26px;align-items:center;background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:24px;margin-top:14px}
.stage-feat img{width:100%;border-radius:12px;box-shadow:0 10px 24px -16px rgba(23,19,13,.5)}
.stage-feat h4{font-family:'Cormorant Garamond',serif;font-size:1.5rem;margin:0 0 6px}
.stage-feat p{margin:0 0 6px;color:var(--muted);font-size:.95rem}

/* ---------- bibliographie ---------- */
.biblio-collapse{margin-top:30px;border:1px solid var(--line);border-radius:14px;overflow:hidden;background:#fff}
.biblio-collapse>summary{list-style:none;cursor:pointer;padding:17px 24px;display:flex;align-items:center;gap:12px;font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:600}
.biblio-collapse>summary::-webkit-details-marker{display:none}
.biblio-collapse>summary::after{content:"+";margin-left:auto;font-size:1.6rem;color:var(--vermillion);line-height:1}
.biblio-collapse[open]>summary::after{content:"\2212"}
.biblio-collapse .bc{font-family:'Inter';font-size:.8rem;color:var(--muted);font-weight:500}
.biblio-collapse .biblio-tabs{margin:0 24px}
.biblio-collapse .biblio-grid{margin:22px 24px 26px}
.biblio-tabs{display:flex;gap:10px;margin-top:34px;flex-wrap:wrap}
.biblio-tab{padding:9px 18px;border-radius:999px;border:1.5px solid var(--line);background:#fff;cursor:pointer;font-weight:600;font-size:.85rem;color:var(--muted);transition:.25s}
.biblio-tab.active{background:var(--ink);color:var(--paper);border-color:var(--ink)}
.biblio-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:22px;margin-top:26px}
.book{text-align:center}
.book .cover{aspect-ratio:2/3;border-radius:8px;overflow:hidden;box-shadow:0 12px 28px -16px rgba(23,19,13,.55);background:var(--paper-3);display:grid;place-items:center}
.book .cover img{width:100%;height:100%;object-fit:cover}
.book .cover.na{color:var(--muted);font-size:.72rem;padding:10px;text-align:center}
.book b{display:block;font-size:.88rem;margin:12px 0 2px;line-height:1.3}
.book span{font-size:.8rem;color:var(--muted)}
.biblio-hidden{display:none}

/* ---------- glossaire ---------- */
.gloss-search{position:relative;max-width:460px;margin:30px 0 8px}
.gloss-search input{width:100%;padding:15px 18px 15px 46px;border-radius:999px;border:1.5px solid var(--line);background:#fff;font-family:inherit;font-size:1rem}
.gloss-search svg{position:absolute;left:16px;top:50%;transform:translateY(-50%);width:20px;height:20px;stroke:var(--muted)}
.gloss-count{font-size:.85rem;color:var(--muted);margin-bottom:18px}
.gloss-sec{margin-top:16px;border:1px solid var(--line);border-radius:12px;background:#fff;overflow:hidden}
.gloss-sec>summary{cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:12px;padding:15px 18px;font-family:'Cormorant Garamond',serif;font-size:1.35rem;font-weight:600;color:var(--vermillion)}
.gloss-sec>summary::-webkit-details-marker{display:none}
.gloss-sec>summary .gc{font-family:'Inter',sans-serif;font-size:.8rem;color:var(--muted);font-weight:500}
.gloss-sec>summary::after{content:"+";font-family:'Inter',sans-serif;color:var(--vermillion);font-size:1.4rem;line-height:1;margin-left:auto}
.gloss-sec[open]>summary::after{content:"−"}
.gloss-list{display:grid;grid-template-columns:repeat(2,1fr);gap:0 34px;padding:0 18px 12px;min-width:0}
.gloss-row{display:grid;grid-template-columns:auto 1fr;gap:14px;padding:9px 0;border-bottom:1px dashed var(--line);align-items:baseline;min-width:0}
.gloss-row .gt{font-weight:700;color:var(--ink);overflow-wrap:anywhere;min-width:0}
.gloss-row .gp{font-style:italic;color:var(--vermillion);font-size:.82rem}
.gloss-row .gd{color:var(--muted);font-size:.92rem;overflow-wrap:anywhere;min-width:0}
.gloss-empty{color:var(--muted);padding:20px 0}

/* ---------- faq ---------- */
.faq{max-width:820px;margin:44px auto 0}
.faq-item{border-bottom:1px solid var(--line)}
.faq-q{width:100%;text-align:left;background:none;border:0;cursor:pointer;padding:22px 40px 22px 0;font-family:'Cormorant Garamond',serif;font-size:1.35rem;color:var(--ink);position:relative;font-weight:600}
.faq-q::after{content:"+";position:absolute;right:6px;top:50%;transform:translateY(-50%);font-size:1.6rem;color:var(--vermillion);transition:.3s;font-family:'Inter'}
.faq-item.open .faq-q::after{content:"−"}
.faq-a{max-height:0;overflow:hidden;transition:max-height .35s ease}
.faq-a p{margin:0 0 20px;color:var(--muted)}

@media(max-width:960px){
  .gallery-grid{grid-template-columns:repeat(3,1fr)}
  .gloss-list{grid-template-columns:1fr}
  .stage-feat{grid-template-columns:1fr}
  .stage-feat img{max-width:220px}
}
@media(max-width:640px){
  .gallery-grid{grid-template-columns:repeat(2,1fr)}
  .musubi-quote{padding:32px 26px}
  .musubi-quote p.q{font-size:1.25rem}
  /* glossaire : lignes empilées, pas de débordement */
  .gloss-row{grid-template-columns:1fr;gap:2px;padding:10px 0}
  .gloss-row .gt{font-size:.98rem}
  .gloss-sec>summary{font-size:1.2rem;padding:14px 16px}
  .gloss-list{padding:0 16px 12px}
  /* frise : ligne et pastilles à gauche, texte sans chevauchement */
  .tl::before{left:7px}
  .tl-item{grid-template-columns:1fr;gap:2px;padding-left:34px}
  .tl-item .yr{text-align:left;font-size:1.4rem}
  .tl-item .yr::after{left:-30px;right:auto;top:9px}
  .tl-item p{max-width:none}
}
/* ---------- scrollspy active nav ---------- */
.menu a.active{color:var(--gold)}
header.scrolled .menu a.active{color:var(--vermillion)}
.menu a.active{font-weight:600}

/* ---------- floating mobile CTA ---------- */
.mobile-cta{position:fixed;left:16px;bottom:22px;z-index:50;display:none;align-items:center;gap:8px;
  background:var(--vermillion);color:#fff;padding:13px 20px;border-radius:999px;font-weight:600;font-size:.9rem;
  box-shadow:0 14px 30px -10px rgba(195,48,37,.7);opacity:0;transform:translateY(20px);transition:.35s;text-decoration:none}
.mobile-cta.show{opacity:1;transform:none}
.mobile-cta svg{width:18px;height:18px;stroke:#fff}

/* ---------- quick contact ---------- */
.quick-contact{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
.qc{display:inline-flex;align-items:center;gap:9px;padding:12px 18px;border-radius:12px;border:1px solid rgba(247,241,230,.2);
  color:var(--paper);font-weight:600;font-size:.9rem;transition:.25s;background:rgba(247,241,230,.05)}
.qc:hover{background:var(--gold);color:var(--ink);border-color:var(--gold);transform:translateY(-3px)}
.qc svg{width:18px;height:18px}
.qc.wa:hover{background:#25d366;color:#fff;border-color:#25d366}

/* ---------- grades & armes ---------- */
.grades{background:var(--paper-2)}
.belt-track{display:flex;flex-wrap:wrap;gap:10px;margin-top:30px;align-items:stretch}
.belt{flex:1;min-width:120px;background:#fff;border:1px solid var(--line);border-radius:12px;padding:16px 14px;text-align:center}
.belt .bar{height:8px;border-radius:4px;margin-bottom:12px}
.belt b{display:block;font-family:'Cormorant Garamond',serif;font-size:1.15rem;line-height:1.1}
.belt span{font-size:.78rem;color:var(--muted)}
.arme-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:20px}
.arme{background:#fff;border:1px solid var(--line);border-radius:var(--radius);padding:28px 26px;transition:.3s}
.arme:hover{transform:translateY(-5px);box-shadow:var(--shadow)}
.arme .jp{font-size:2.2rem;color:var(--vermillion);line-height:1}
.arme h4{font-family:'Cormorant Garamond',serif;font-size:1.5rem;margin:8px 0 8px}
.arme p{margin:0;color:var(--muted);font-size:.95rem}

@media(max-width:760px){.arme-grid{grid-template-columns:1fr}}
@media(max-width:640px){.mobile-cta{display:inline-flex}}

/* ---------- print ---------- */
@media print{
  header,.scrolldown,.mobile-cta,.top,.lb,.hero-waves,.hero-kanji,.burger,form,.gloss-search,
  #galerie,#videos,#glossaire,#bibliographie,.social,.biblio-tabs,.scrolldown{display:none!important}
  *{color:#000!important;background:#fff!important;box-shadow:none!important;text-shadow:none!important}
  body{font-size:12pt}
  .hero{min-height:auto;padding:0}
  .hero-inner{padding:10px 0}
  .hero h1{font-size:32pt}
  .section{padding:14px 0;page-break-inside:avoid}
  .card,.teacher,.newscard{border:1px solid #ccc!important;page-break-inside:avoid}
  a[href^="http"]::after{content:" (" attr(href) ")";font-size:9pt;color:#555!important}
  .map-row iframe{display:none}
}
/* ---------- alert bar ---------- */
.alertbar{position:fixed;top:0;left:0;right:0;z-index:70;background:var(--vermillion);color:#fff;text-align:center;padding:11px 16px;font-weight:600;font-size:.92rem}
body.has-alert header{top:42px}
@media(max-width:640px){.alertbar{font-size:.82rem;padding:9px 12px}}

/* ---------- stages ---------- */
.stages-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px;margin-top:44px}
.stage-card{background:#fff;border:1px solid var(--line);border-radius:var(--radius);overflow:hidden;transition:.3s;display:flex;flex-direction:column}
.stage-card:hover{transform:translateY(-5px);box-shadow:var(--shadow)}
.stage-card .ph{height:200px;background:var(--paper-3);overflow:hidden}
.stage-card .ph img{width:100%;height:100%;object-fit:cover}
.stage-card .body{padding:22px 24px}
.stage-card .d{font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;color:var(--vermillion);font-weight:700}
.stage-card h3{font-size:1.4rem;margin:6px 0 6px}
.stage-card p{margin:0;color:var(--muted);font-size:.92rem}
.stages-empty{margin-top:40px;text-align:center;color:var(--muted);max-width:60ch;margin-left:auto;margin-right:auto}

/* ---------- facebook ---------- */
.fb-card{display:flex;align-items:center;gap:22px;max-width:560px;margin:40px auto 0;background:#fff;border:1px solid var(--line);border-radius:16px;padding:22px 26px;transition:.3s;color:var(--ink)}
.fb-card:hover{transform:translateY(-3px);box-shadow:var(--shadow);border-color:#1877f2}
.fb-card .fb-ic{flex:0 0 auto;width:54px;height:54px;border-radius:14px;background:#1877f2;display:grid;place-items:center}
.fb-card .fb-ic svg{width:28px;height:28px;fill:#fff}
.fb-card .fb-txt{flex:1;line-height:1.35}
.fb-card .fb-txt b{display:block;font-family:'Cormorant Garamond',serif;font-size:1.35rem}
.fb-card .fb-txt span{color:var(--muted);font-size:.9rem}
.fb-card .fb-go{flex:0 0 auto;color:#1877f2;font-size:1.5rem;font-weight:700;transition:.25s}
.fb-card:hover .fb-go{transform:translateX(4px)}
@media(max-width:560px){.fb-card{flex-direction:column;text-align:center;gap:14px}}

/* ---------- light form (essai) ---------- */
.form-light{display:grid;gap:16px}
.form-light label{font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:-8px;font-weight:600}
.form-light input,.form-light textarea{background:#fff;border:1px solid var(--line);color:var(--ink)}
.form-light input:focus,.form-light textarea:focus{border-color:var(--vermillion);background:#fff}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:560px){.grid2{grid-template-columns:1fr}}
/* agenda AFA (flux RSS) */
.afa-list{display:grid;gap:12px;margin:40px auto 0;max-width:840px}
.afa-ev{display:flex;align-items:center;gap:20px;background:#fff;border:1px solid var(--line);border-radius:14px;padding:15px 22px;transition:.25s;color:var(--ink)}
.afa-ev:hover{border-color:var(--vermillion);transform:translateY(-2px);box-shadow:0 12px 26px -20px rgba(195,48,37,.4)}
.afa-date{flex:0 0 auto;text-align:center;width:52px;line-height:1}
.afa-date .dd{display:block;font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;color:var(--vermillion)}
.afa-date .mm{display:block;font-size:.64rem;letter-spacing:.12em;color:var(--muted);margin-top:2px}
.afa-ev .t{flex:1;font-weight:600;font-size:.98rem}
.afa-ev .go{flex:0 0 auto;color:var(--vermillion);font-size:1.3rem;transition:.25s}
.afa-ev:hover .go{transform:translateX(4px)}
.afa-src{text-align:center;margin-top:22px;font-size:.92rem;color:var(--muted)}
.afa-src a{color:var(--vermillion);font-weight:600}
@media(max-width:560px){.afa-ev{gap:14px;padding:13px 16px}.afa-date{width:44px}.afa-date .dd{font-size:1.4rem}}

/* ============================================================
   THEME "OR" — déclinaison nocturne & or (?theme=or)
   Fond noir chaud + accents or, d'après le logo doré du dojo.
   ============================================================ */
html.theme-or{
  --ink:#f4ebd7;          /* texte clair */
  --paper:#0e0b06;        /* fond sombre principal */
  --paper-2:#141009;      /* sections alternées */
  --paper-3:#241c10;      /* puces / vignettes */
  --vermillion:#e6c25c;   /* accent principal = or */
  --vermillion-d:#d3a93f;
  --gold:#e9cb6e;
  --muted:#b7a886;
  --line:rgba(233,203,110,.16);
  --shadow:0 20px 50px -22px rgba(0,0,0,.8);
}
/* surfaces qui utilisaient --ink en FOND : les garder sombres */
.theme-or .strip{background:#100c06;color:#f4ebd7}
.theme-or .history{background:#141009;color:#f4ebd7}
.theme-or .contact{background:#100c06;color:#f4ebd7}
.theme-or .biblio-tab.active{background:var(--vermillion);color:#17130d;border-color:var(--vermillion)}
/* header translucide au scroll */
.theme-or header.scrolled{background:rgba(14,11,6,.92);box-shadow:0 1px 0 rgba(233,203,110,.16)}
/* cartes claires -> surfaces sombres */
.theme-or .value,.theme-or .card,.theme-or .teacher,.theme-or .vid,.theme-or .newscard,
.theme-or .stage-feat,.theme-or .stage-card,.theme-or .arme,.theme-or .belt,
.theme-or .biblio-tab,.theme-or .gloss-sec,.theme-or .gloss-search input,.theme-or .afa-ev,.theme-or .biblio-collapse,.theme-or .fb-card,
.theme-or .contact form input,.theme-or .contact form textarea,
.theme-or .form-light input,.theme-or .form-light textarea{
  background:#1a140b;border-color:var(--line);color:var(--ink)
}
.theme-or .contact form input::placeholder,.theme-or .contact form textarea::placeholder,
.theme-or .form-light input::placeholder,.theme-or .gloss-search input::placeholder{color:#8f8266}
.theme-or .contact form input:focus,.theme-or .contact form textarea:focus,
.theme-or .form-light input:focus,.theme-or .form-light textarea:focus{background:#1f1810;border-color:var(--vermillion)}
.theme-or .afa-ev:hover{border-color:var(--vermillion)}
.theme-or .newscard{box-shadow:0 12px 34px -24px #000}
.theme-or .card:hover,.theme-or .teacher:hover,.theme-or .arme:hover,
.theme-or .vid.link:hover,.theme-or .stage-card:hover{border-color:var(--vermillion)}
/* textes secondaires en var(--paper) sur fond sombre -> clairs */
.theme-or .cinfo .row a,.theme-or .cinfo .row span,.theme-or .qc{color:#f4ebd7}
/* textes d'accent posés sur l'or -> encre sombre */
.theme-or .btn-primary{color:#17130d;box-shadow:0 14px 30px -12px rgba(233,203,110,.45)}
.theme-or .btn-primary:hover{color:#17130d}
.theme-or .menu a.cta{color:#17130d!important}
.theme-or .mobile-cta{color:#17130d}
.theme-or .mobile-cta svg{stroke:#17130d}
.theme-or .alertbar,.theme-or .skip{color:#17130d}
.theme-or .top{color:#17130d}
.theme-or .top svg{stroke:#17130d}
.theme-or .qc:hover{color:#17130d}
/* ensō + kanji filigrane -> or */
.theme-or .enso svg path{stroke:#e9cb6e}
.theme-or .enso svg{filter:drop-shadow(0 20px 40px rgba(233,203,110,.16))}
.theme-or .hero-kanji{color:rgba(233,203,110,.09)}
.theme-or .musubi-quote .mk{color:rgba(233,203,110,.12)}
/* panneaux sombres en gradient : texte clair garanti */
.theme-or .memoriam{color:#f4ebd7}
.theme-or .musubi-quote b.term{color:#f4ebd7}
/* footer (toujours sombre) : textes clairs + icônes or */
.theme-or .foot h3{color:#f4ebd7}
.theme-or .social svg{fill:#e9cb6e}
/* menu mobile : panneau sombre + liens clairs */
@media(max-width:640px){
  .theme-or .menu{background:#141009}
  .theme-or .menu a{color:#f4ebd7!important}
}
</style><?php if (config('analytics')) echo "\n" . config('analytics') . "\n"; ?>
<?php if (turnstile_enabled()): ?><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script><?php endif; ?>
</head>
<body class="no-js<?php echo $alertOn ? ' has-alert' : ''; ?>">
<script>document.body.classList.remove('no-js');</script>
<a href="#main" class="skip">Aller au contenu</a>
<?php if ($alertOn): ?><div class="alertbar" role="alert"><?php echo e(settings('alert_text')); ?></div><?php endif; ?>

<header id="head">
  <div class="nav">
    <a href="#accueil" class="brand">
      <img src="<?php echo $logoImg; ?>" alt="Blason Musubi Dojo"/>
      <span style="line-height:1"><b>Musubi Dojo</b><span>Aïkido · Jurbise</span></span>
    </a>
    <nav class="menu" id="menu" aria-label="Navigation principale">
      <a href="#aikido">L'Aïkido</a>
      <a href="#dojo">Le Dojo</a>
      <a href="#professeurs">Professeurs</a>
      <a href="#galerie">Galerie</a>
      <a href="#histoire">Histoire</a>
      <a href="#glossaire">Glossaire</a>
      <a href="#contact" class="cta">Cours d'essai gratuit</a>
    </nav>
    <button class="burger" id="burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="menu"><span></span><span></span><span></span></button>
  </div>
</header>

<main id="main" tabindex="-1">
<!-- ===================== HERO ===================== -->
<section class="hero" id="accueil">
  <div class="hero-waves"></div>
  <div class="hero-kanji jp" aria-hidden="true">結</div>
  <div class="hero-inner">
    <p class="tag"><?php echo e($lib['hero_eyebrow'] ?? "École d'Aïkido Traditionnel · Aïkikaï"); ?></p>
    <h1><?php echo ($lib['hero_title'] ?? "L'art de<br>l'harmonie"); ?><em>.</em></h1>
    <p class="sub"><?php echo e($lib['hero_subtitle'] ?? ''); ?></p>
    <p class="place">Académie Provinciale de Police · Route d'Ath 25-35, 7050 Jurbise</p>
    <?php if ($nextCourse): ?><p class="place" style="color:var(--gold);margin-top:4px">Prochain cours : <?php echo e($nextCourse); ?></p><?php endif; ?>
    <div class="hero-cta">
      <a href="#contact" class="btn btn-primary">Réserver 2 cours d'essai gratuits</a>
      <a href="#aikido" class="btn btn-ghost">Découvrir l'aïkido</a>
    </div>
  </div>
  <div class="scrolldown"><span>Défiler</span><i></i></div>
</section>

<!-- ===================== STRIP ===================== -->
<section class="strip">
  <div class="wrap">
    <div class="stat reveal"><b>1987</b><span>Fondation du dojo</span></div>
    <div class="stat reveal"><b>Aïkikaï</b><span>Aïkido traditionnel</span></div>
    <div class="stat reveal"><b>6 à 99</b><span>Ans, sans limite</span></div>
    <div class="stat reveal"><b>2</b><span>Cours d'essai gratuits</span></div>
  </div>
</section>

<!-- ===================== AIKIDO ===================== -->
<section class="section" id="aikido">
  <div class="wrap split">
    <div class="reveal">
      <p class="eyebrow">Qu'est-ce que l'Aïkido</p>
      <h2 class="h-sec">Un art martial<br>adapté au monde moderne</h2>
      <p class="lead"><?php echo e($aikido['lead'] ?? ''); ?></p>
      <div class="values">
        <?php foreach (($aikido['values'] ?? []) as $v): ?>
        <div class="value"><b><?php echo e($v['title'] ?? ''); ?></b><p><?php echo e($v['text'] ?? ''); ?></p></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="reveal">
      <div class="enso">
        <svg viewBox="0 0 200 200" aria-hidden="true">
          <path d="M100 22 a78 78 0 1 0 44 14" fill="none" stroke="#c33025" stroke-width="13" stroke-linecap="round"
            transform="rotate(20 100 100)" opacity="0.92"/>
        </svg>
        <div class="cn"><span class="k jp">合気道</span><small>Aï · Ki · Dō</small></div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== DOJO / HORAIRES / TARIFS ===================== -->
<section class="section dojo waves" id="dojo">
  <div class="wrap">
    <div class="reveal" style="text-align:center">
      <p class="eyebrow" style="justify-content:center">Le Dojo</p>
      <h2 class="h-sec">Horaires, tarifs &amp; inscription</h2>
      <p class="lead" style="margin:0 auto">L'école est en « portes ouvertes » pendant ses dix mois d'ouverture. Venez pousser la porte du dojo et fouler le tatami.</p>
    </div>

    <div class="cards-3">
      <div class="card reveal">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div>
        <h3>Horaires</h3>
        <ul>
          <?php foreach (($infos['horaires'] ?? []) as $h): ?><li><span><?php echo e($h['label']); ?></span><b><?php echo e($h['value']); ?></b></li>
          <?php endforeach; ?>
        </ul>
        <p class="note"><?php echo e($infos['horaires_note'] ?? ''); ?></p>
        <a href="musubi-horaires.ics" download class="btn btn-dark" style="margin-top:14px;font-size:.8rem;padding:10px 18px">Ajouter à mon agenda</a>
      </div>

      <div class="card reveal">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M12 3v18"/><path d="M17 6H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <h3>Tarifs</h3>
        <ul>
          <?php foreach (($infos['tarifs'] ?? []) as $t): ?><li><span><?php echo e($t['label']); ?></span><b><?php echo e($t['value']); ?></b></li>
          <?php endforeach; ?>
        </ul>
        <p class="note"><?php echo e($infos['tarifs_note'] ?? ''); ?></p>
      </div>

      <div class="card reveal">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
        <h3>Inscription</h3>
        <ul>
          <?php foreach (($infos['inscription'] ?? []) as $i): ?><li><span><?php echo e($i['label']); ?></span><b><?php echo e($i['value']); ?></b></li>
          <?php endforeach; ?>
        </ul>
        <p class="note"><?php echo e($infos['inscription_note'] ?? ''); ?></p>
      </div>
    </div>

    <div class="map-row">
      <div class="addr reveal">
        <h3>Nous trouver</h3>
        <p>Un dojo traditionnel, chaleureux, à deux minutes du pont de Nimy.</p>
        <div class="pins">
          <div class="pin">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
            <div><b>Académie Provinciale de Police de Jurbise</b><br>Route d'Ath 25-35, 7050 Jurbise</div>
          </div>
          <div class="pin">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
            <div>À 2 min du pont de Nimy · Parking sur place</div>
          </div>
        </div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px">
          <a href="https://www.google.com/maps/dir/?api=1&destination=50.50327,3.93139" target="_blank" rel="noopener" class="btn btn-primary">Itinéraire</a>
          <a href="https://www.openstreetmap.org/?mlat=50.5033&mlon=3.9314#map=15/50.5033/3.9314" target="_blank" rel="noopener" class="btn btn-dark">OpenStreetMap</a>
        </div>
      </div>
      <iframe class="reveal" loading="lazy" title="Carte du dojo à Jurbise" src="https://www.openstreetmap.org/export/embed.html?bbox=3.8510513305664067%2C50.465372121864185%2C4.011726379394532%2C50.54125387068491&layer=mapnik&marker=50.50327364589812%2C3.9313888549804688"></iframe>
    </div>
  </div>
</section>

<!-- ===================== TEACHERS ===================== -->
<section class="section" id="professeurs">
  <div class="wrap">
    <div class="reveal" style="text-align:center">
      <p class="eyebrow" style="justify-content:center">L'enseignement</p>
      <h2 class="h-sec">Nos professeurs</h2>
      <p class="lead" style="margin:0 auto">Une transmission directe, dans la lignée de Sugano Shihan et du Hombu Dojo de Tokyo.</p>
    </div>
    <div class="teachers">
      <?php foreach ($profs as $p): $pPhoto = $p['photo'] ?? ''; $pWebp = $p['webp'] ?? ''; if ($themeOr) { $pPhoto = preg_replace('#(audrey|roberto)\.jpg$#', '$1-or.jpg', $pPhoto); $pWebp = preg_replace('#(audrey|roberto)\.webp$#', '$1-or.webp', $pWebp); } ?>
      <div class="teacher reveal">
        <div class="ph"><?php echo picture($pPhoto, $pWebp, 'Portrait de ' . ($p['name'] ?? ''), 'loading="lazy" width="400" height="300" style="object-position:' . e($p['pos'] ?? '50% 30%') . '"'); ?></div>
        <div class="body">
          <span class="role"><?php echo e($p['role'] ?? ''); ?></span>
          <h3><?php echo e($p['name'] ?? ''); ?></h3>
          <p class="grade"><?php echo e($p['grade'] ?? ''); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <p class="lead reveal" style="margin:30px auto 0;text-align:center"><?php echo e($lib['director'] ?? ''); ?></p>
  </div>
</section>

<!-- ===================== GALERIE ===================== -->
<section class="section dojo waves" id="galerie">
  <div class="wrap">
    <div class="reveal" style="text-align:center">
      <p class="eyebrow" style="justify-content:center">En images</p>
      <h2 class="h-sec">La vie sur le tatami</h2>
      <p class="lead" style="margin:0 auto">Quelques instants de pratique dans notre dojo traditionnel.</p>
    </div>
    <div class="gallery-grid">
      <?php foreach ($galerie as $idx => $g): $full = $g['img'] ?? ''; ?>
      <button class="gal-item reveal" data-full="<?php echo e($full); ?>" aria-label="Agrandir la photo <?php echo $idx + 1; ?>"><?php echo picture($full, $g['webp'] ?? '', $g['alt'] ?? '', 'loading="lazy" width="400" height="300"'); ?></button>
      <?php endforeach; ?>
    </div>
    <p class="gal-note reveal">Membres du club : de nouvelles photos en haute résolution sont les bienvenues pour enrichir cette galerie.</p>
  </div>
</section>

<!-- ===================== HISTORY ===================== -->
<section class="section history" id="histoire">
  <div class="hero-kanji jp" aria-hidden="true" style="left:-6vw;right:auto">道</div>
  <div class="wrap" style="position:relative;z-index:2">
    <div class="reveal">
      <p class="eyebrow">Notre histoire</p>
      <h2 class="h-sec" style="color:#f4ebd7">Bientôt quatre décennies<br>sur le tatami</h2>
    </div>
    <div class="tl">
      <?php foreach ($histoire as $h): ?>
      <div class="tl-item reveal"><div class="yr"><?php echo e($h['year'] ?? ''); ?></div><p><?php echo rich($h['text'] ?? ''); ?></p></div>
      <?php endforeach; ?>
    </div>
    <div class="musubi-quote reveal">
      <span class="mk jp" aria-hidden="true">結</span>
      <p class="lbl">Le sens de notre nom</p>
      <b class="term">Musubi</b>
      <p class="phon">musubi · mouzoubi</p>
      <p class="q">« Processus d'unification des contraires. Est mouvement, car sans mouvement, l'union est impossible. Symbolisé par la spirale qui recycle constamment son énergie. Ni commencement, ni fin. Toute activité est un processus de mutation, et la seule constante dans l'Univers est le changement. »</p>
      <p class="src">Saotome Senseï</p>
      <p class="nb">Ce nom a été choisi par Sugano Shihan sur proposition du professeur.</p>
    </div>
  </div>
</section>

<!-- ===================== VIDEOS ===================== -->
<?php $techMedia = array_filter($techniques, fn($t) => !empty($t['img']) || trim($t['url'] ?? '') !== ''); ?>
<?php if ($techMedia): ?>
<section class="section" id="videos">
  <div class="wrap">
    <div class="reveal" style="text-align:center">
      <p class="eyebrow" style="justify-content:center">Techniques</p>
      <h2 class="h-sec">Le répertoire du dojo</h2>
      <p class="lead" style="margin:0 auto">Un aperçu des techniques travaillées : mains nues, tanto (couteau), ken (sabre) et jo (bâton).</p>
    </div>
    <div class="vid-grid">
      <?php $ti = 0; foreach ($techniques as $tech): $u = trim($tech['url'] ?? ''); $img = $tech['img'] ?? ''; $ti++; $n = str_pad($ti, 2, '0', STR_PAD_LEFT);
        $lead = $img !== '' ? picture($img, $tech['webp'] ?? '', $tech['t'] ?? '', 'class="vid-thumb" loading="lazy" width="56" height="56"') : '<span class="n">' . $n . '</span>';
        $inner = $lead . '<div><b>' . e($tech['t'] ?? '') . '</b><span>' . e($tech['d'] ?? '') . '</span></div>';
      ?>
      <?php if ($u !== ''): ?>
      <a class="vid link reveal" href="<?php echo e($u); ?>" target="_blank" rel="noopener"><?php echo $inner; ?><span class="vid-go" aria-hidden="true">▸</span></a>
      <?php else: ?>
      <div class="vid reveal"><?php echo $inner; ?></div>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== GRADES & ARMES ===================== -->
<section class="section grades waves" id="grades">
  <div class="wrap">
    <div class="reveal">
      <p class="eyebrow">La progression</p>
      <h2 class="h-sec">Grades &amp; armes</h2>
      <p class="lead"><?php echo e($armes['grades_intro'] ?? ''); ?></p>
    </div>
    <div class="belt-track reveal">
      <?php foreach (($armes['belts'] ?? []) as $b): $bar = $b['bar'] ?? '#e7dcc6'; $border = (strpos($bar, 'gradient') === false && $bar !== '#17130d') ? ';border:1px solid var(--line)' : ''; ?>
      <div class="belt"><div class="bar" style="background:<?php echo e($bar); ?><?php echo $border; ?>"></div><b><?php echo e($b['label'] ?? ''); ?></b><span><?php echo e($b['sub'] ?? ''); ?></span></div>
      <?php endforeach; ?>
    </div>

    <div class="reveal" style="margin-top:56px">
      <h3 class="serif" style="font-size:1.8rem;margin:0 0 6px">Les armes du dojo</h3>
      <p class="lead" style="margin:0"><?php echo e($armes['armes_intro'] ?? ''); ?></p>
    </div>
    <div class="arme-grid">
      <?php foreach (($armes['armes'] ?? []) as $a): ?>
      <div class="arme reveal"><span class="jp"><?php echo e($a['kanji'] ?? ''); ?></span><h4><?php echo e($a['nom'] ?? ''); ?></h4><p><?php echo e($a['desc'] ?? ''); ?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== AGENDA STAGES ===================== -->
<section class="section" id="agenda">
  <div class="wrap">
    <div class="reveal" style="text-align:center">
      <p class="eyebrow" style="justify-content:center">Agenda</p>
      <h2 class="h-sec">Prochains stages</h2>
      <p class="lead" style="margin:0 auto">Le calendrier des stages de l'Association Francophone d'Aïkido, mis à jour automatiquement.</p>
    </div>
    <?php if ($stages): ?>
    <div class="stages-list">
      <?php foreach ($stages as $s): $sts = strtotime($s['date'] ?? ''); ?>
      <article class="stage-card reveal">
        <?php if (!empty($s['image'])): ?><div class="ph"><?php echo picture($s['image'], $s['webp'] ?? '', $s['title'] ?? '', 'loading="lazy"'); ?></div><?php endif; ?>
        <div class="body">
          <p class="d"><?php echo $sts ? e(fmt_date_fr($sts)) : e($s['date'] ?? ''); ?></p>
          <h3><?php echo e($s['title'] ?? ''); ?></h3>
          <p><?php echo e(trim(($s['teacher'] ?? '') . (!empty($s['grade']) ? ', ' . $s['grade'] : ''))); if (!empty($s['place'])) echo ' · ' . e($s['place']); ?></p>
          <?php if (!empty($s['link'])): ?><a href="<?php echo e($s['link']); ?>" target="_blank" rel="noopener" class="btn btn-dark" style="margin-top:14px">En savoir plus</a><?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if ($afa): $moAbbr = [1 => 'JAN', 'FÉV', 'MAR', 'AVR', 'MAI', 'JUIN', 'JUIL', 'AOÛT', 'SEP', 'OCT', 'NOV', 'DÉC']; ?>
    <div class="afa-list">
      <?php foreach ($afa as $ev): ?>
      <a class="afa-ev reveal" href="<?php echo e($ev['link']); ?>" target="_blank" rel="noopener">
        <span class="afa-date"><span class="dd"><?php echo (int) date('j', $ev['ts']); ?></span><span class="mm"><?php echo $moAbbr[(int) date('n', $ev['ts'])]; ?></span></span>
        <span class="t"><?php echo e($ev['title']); ?></span>
        <span class="go" aria-hidden="true">&rarr;</span>
      </a>
      <?php endforeach; ?>
    </div>
    <p class="afa-src reveal">Agenda complet et inscriptions sur <a href="https://afamanager.aikido.be/fr/evenement/" target="_blank" rel="noopener">le site de l'AFA</a>.</p>
    <?php
      $events = array_map(fn($ev) => ['@context' => 'https://schema.org', '@type' => 'Event', 'name' => $ev['title'],
              'startDate' => date('Y-m-d', $ev['ts']), 'url' => $ev['link'],
              'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
              'location' => ['@type' => 'Place', 'name' => 'Belgique'],
              'organizer' => ['@type' => 'Organization', 'name' => "Association Francophone d'Aïkido"]], $afa);
      echo '<script type="application/ld+json">' . json_encode(count($events) === 1 ? $events[0] : $events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
    ?>
    <?php elseif (!$stages): ?>
    <p class="stages-empty reveal">Aucun stage n'est programmé pour l'instant. Retrouvez l'agenda complet des stages fédéraux sur <a href="https://afamanager.aikido.be/fr/evenement/" target="_blank" rel="noopener" style="color:var(--vermillion);font-weight:600">le site de l'AFA</a>.</p>
    <?php endif; ?>
  </div>
</section>

<!-- ===================== NEWS / MEMORIAM ===================== -->
<section class="section news" id="actualites">
  <div class="wrap">
    <div class="reveal">
      <p class="eyebrow">Actualités &amp; stages</p>
      <h2 class="h-sec">La vie du dojo</h2>
    </div>
    <div class="news-grid">
      <div id="news-col">
        <?php foreach ($actus as $n): ?>
        <div class="newscard reveal" style="margin-bottom:26px">
          <p class="date"><?php echo e($n['date'] ?? ''); ?></p>
          <h3><?php echo e($n['title'] ?? ''); ?></h3>
          <?php if (!empty($n['image'])): ?>
          <div style="display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap">
            <?php echo picture($n['image'], $n['imageWebp'] ?? '', $n['imageAlt'] ?? '', 'loading="lazy" width="150" height="200" style="width:150px;height:auto;border-radius:10px;box-shadow:0 10px 24px -16px rgba(23,19,13,.5)"'); ?>
            <div style="flex:1;min-width:180px"><?php if (!empty($n['text'])): ?><p style="margin:0"><?php echo e($n['text']); ?></p><?php endif; ?></div>
          </div>
          <?php elseif (!empty($n['text'])): ?>
          <p><?php echo e($n['text']); ?></p>
          <?php endif; ?>
          <?php if (!empty($n['link'])): ?><a href="<?php echo e($n['link']); ?>" target="_blank" rel="noopener" class="btn btn-dark" style="margin-top:16px"><?php echo e($n['linkLabel'] ?: 'En savoir plus'); ?></a><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="memoriam reveal">
        <p class="lbl">In Memoriam</p>
        <?php $founders = $memoriam['founders'] ?? (isset($memoriam['founder']) ? [$memoriam['founder']] : []); ?>
        <div class="mem-founders">
          <?php foreach ($founders as $mf): ?>
          <figure>
            <?php echo picture($mf['photo'] ?? '', $mf['webp'] ?? '', $mf['name'] ?? '', 'class="mimg" width="120" height="120"'); ?>
            <figcaption><b><?php echo e($mf['name'] ?? ''); ?></b><span><?php echo e($mf['subtitle'] ?? ''); ?></span></figcaption>
          </figure>
          <?php endforeach; ?>
        </div>
        <?php if (!empty($memoriam['founder_text'])): ?><p><?php echo e($memoriam['founder_text']); ?></p><?php endif; ?>
        <?php if (!empty($memoriam['masters'])): ?>
        <div class="mem-masters">
          <?php foreach ($memoriam['masters'] as $ms): ?>
          <figure><?php echo picture($ms['photo'] ?? '', $ms['webp'] ?? '', $ms['name'] ?? '', 'loading="lazy" width="74" height="74"'); ?><figcaption><b><?php echo e($ms['name'] ?? ''); ?></b><?php echo e($ms['years'] ?? ''); ?></figcaption></figure>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ===================== BIBLIOGRAPHIE ===================== -->
<section class="section" id="bibliographie">
  <div class="wrap">
    <div class="reveal">
      <p class="eyebrow">Pour aller plus loin</p>
      <h2 class="h-sec">Bibliographie</h2>
      <p class="lead">Une sélection d'ouvrages de référence sur l'aïkido et les arts martiaux japonais, conseillés par le dojo.</p>
    </div>
    <details class="biblio-collapse reveal">
      <summary>Afficher la sélection <span class="bc"><?php echo count($biblio); ?> ouvrages</span></summary>
      <div class="biblio-tabs">
        <button class="biblio-tab active" data-cat="all">Tous</button>
        <button class="biblio-tab" data-cat="fr">Français</button>
        <button class="biblio-tab" data-cat="en">Anglais</button>
        <button class="biblio-tab" data-cat="autres">Autres</button>
      </div>
      <div class="biblio-grid" id="biblio-grid"></div>
    </details>
  </div>
</section>

<!-- ===================== GLOSSAIRE ===================== -->
<section class="section dojo waves" id="glossaire">
  <div class="wrap">
    <div class="reveal">
      <p class="eyebrow">Le vocabulaire du tatami</p>
      <h2 class="h-sec">Glossaire de l'aïkido</h2>
      <p class="lead">Les termes japonais entendus au dojo, avec leur prononciation et leur signification. Tapez un mot pour filtrer.</p>
      <div class="gloss-search">
        <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" id="gloss-input" placeholder="Rechercher un terme, une traduction..." aria-label="Rechercher dans le glossaire"/>
      </div>
      <p class="gloss-count" id="gloss-count"></p>
    </div>
    <div class="reveal" id="gloss-body"></div>
    <p class="gal-note reveal" style="text-align:left;margin-top:26px">Vous pouvez aussi <a href="fichiers/Glossaire.xls" style="color:var(--vermillion);font-weight:600">télécharger le glossaire complet</a> (fichier Excel).</p>
  </div>
</section>

<!-- ===================== FAQ ===================== -->
<section class="section" id="faq">
  <div class="wrap">
    <div class="reveal" style="text-align:center">
      <p class="eyebrow" style="justify-content:center">Première visite</p>
      <h2 class="h-sec">Questions fréquentes</h2>
    </div>
    <div class="faq">
      <?php foreach ($faq as $qa): ?>
      <div class="faq-item reveal"><button class="faq-q" aria-expanded="false"><?php echo e($qa['q'] ?? ''); ?></button><div class="faq-a"><p><?php echo rich($qa['a'] ?? ''); ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== FACEBOOK ===================== -->
<?php $fb = settings('facebook_url'); if ($fb): ?>
<section class="section dojo waves" id="facebook-feed">
  <div class="wrap">
    <div class="reveal" style="text-align:center">
      <p class="eyebrow" style="justify-content:center">Communauté</p>
      <h2 class="h-sec">Suivez-nous sur Facebook</h2>
      <p class="lead" style="margin:0 auto">Actualités, photos et vie du club au quotidien.</p>
    </div>
    <a class="fb-card reveal" href="<?php echo e($fb); ?>" target="_blank" rel="noopener">
      <span class="fb-ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/></svg></span>
      <span class="fb-txt"><b>Musubi Dojo Jurbise</b><span>Actualités, photos et publications du club sur Facebook</span></span>
      <span class="fb-go" aria-hidden="true">&rarr;</span>
    </a>
  </div>
</section>
<?php endif; ?>

<!-- ===================== CONTACT ===================== -->
<section class="section contact" id="contact">
  <div class="wrap">
    <div class="reveal">
      <p class="eyebrow">Rejoignez-nous</p>
      <h2 class="h-sec" style="color:#f4ebd7">Venez essayer,<br>c'est gratuit</h2>
      <p class="lead" style="color:rgba(247,241,230,.75)">Deux cours d'essai offerts, sans engagement. Un simple training suffit pour commencer.</p>
    </div>
    <div class="contact-grid">
      <div class="cinfo reveal">
        <div class="row">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></svg>
          <div><b>Téléphone</b><a href="tel:<?php echo e($contact['phone1_tel'] ?? ''); ?>"><?php echo e($contact['phone1'] ?? ''); ?></a><?php if (!empty($contact['phone2'])): ?> · <span><?php echo e($contact['phone2']); ?></span><?php endif; ?></div>
        </div>
        <div class="row">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
          <div><b>E-mail</b><a href="mailto:<?php echo e($contact['email'] ?? ''); ?>"><?php echo e($contact['email'] ?? ''); ?></a></div>
        </div>
        <div class="row">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M21 10c0 6-9 12-9 12s-9-6-9-12a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></svg>
          <div><b>Adresse</b><span><?php echo e($contact['address'] ?? ''); ?></span></div>
        </div>
        <?php $cfb = $contact['facebook'] ?? ''; if ($cfb): ?>
        <div class="row">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="1.7"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3Z"/></svg>
          <div><b>Suivez-nous</b><a href="<?php echo e($cfb); ?>" target="_blank" rel="noopener"><?php echo e(preg_replace('#^https?://(www\.)?#', '', rtrim($cfb, '/'))); ?></a></div>
        </div>
        <?php endif; ?>
        <div class="quick-contact">
          <a class="qc" href="tel:<?php echo e($contact['phone1_tel'] ?? ''); ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.4-1.2a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2Z"/></svg>Appeler</a>
          <?php if (!empty($contact['whatsapp'])): ?><a class="qc wa" href="<?php echo e($contact['whatsapp']); ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.4A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.1-1.1l-.3-.2-2.9.8.8-2.8-.2-.3A8 8 0 1 1 12 20Zm4.4-6c-.2-.1-1.4-.7-1.6-.8s-.4-.1-.5.1-.6.8-.7 1-.3.2-.5.1a6.5 6.5 0 0 1-3.3-2.9c-.2-.4.2-.4.6-1.2a.4.4 0 0 0 0-.4l-.8-1.9c-.2-.5-.4-.4-.5-.4h-.5a.9.9 0 0 0-.7.3A2.8 2.8 0 0 0 6 8.4a4.8 4.8 0 0 0 1 2.5 11 11 0 0 0 4.2 3.7c1.5.6 2 .7 2.8.6a2.4 2.4 0 0 0 1.6-1.1 2 2 0 0 0 .1-1.1c-.1-.1-.3-.2-.5-.3Z"/></svg>WhatsApp</a><?php endif; ?>
          <?php if (!empty($contact['maps'])): ?><a class="qc" href="<?php echo e($contact['maps']); ?>" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 11 19-9-9 19-2-8-8-2Z"/></svg>Itinéraire</a><?php endif; ?>
          <a class="qc" href="musubi-dojo.vcf" download><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>Carte de visite</a>
        </div>
      </div>
      <?php if (($_GET['err'] ?? '') === '1'): ?>
      <p class="reveal" style="color:#ffcabf;background:rgba(195,48,37,.15);border:1px solid rgba(195,48,37,.4);padding:12px 16px;border-radius:12px">Une erreur est survenue ou le formulaire était incomplet. Merci de réessayer.</p>
      <?php endif; ?>
      <form class="reveal" action="contact.php" method="POST">
        <input type="hidden" name="type" value="contact"/>
        <input type="hidden" name="ftok" value="<?php echo e(form_token()); ?>"/>
        <input type="text" name="website" tabindex="-1" autocomplete="off" style="display:none" aria-hidden="true"/>
        <label for="nom">Nom</label>
        <input id="nom" name="nom" type="text" placeholder="Votre nom" required/>
        <label for="mail">E-mail</label>
        <input id="mail" name="email" type="email" placeholder="vous@exemple.be" required/>
        <label for="msg">Message</label>
        <textarea id="msg" name="message" placeholder="Je souhaite venir essayer un cours..." required></textarea>
        <?php echo turnstile_widget(); ?>
        <button type="submit" class="btn btn-primary" style="justify-content:center">Envoyer ma demande</button>
      </form>
    </div>
  </div>
</section>

</main>

<!-- ===================== FOOTER ===================== -->
<footer>
  <div class="foot">
    <div>
      <a href="#accueil" class="brand" style="color:#f4ebd7">
        <img src="<?php echo $logoImg; ?>" alt="Musubi Dojo" style="width:52px;height:52px;border-radius:50%"/>
        <span style="line-height:1.1"><b style="font-size:1.5rem">Musubi Dojo</b><span>Aïkido Traditionnel · Jurbise</span></span>
      </a>
      <p style="margin-top:18px"><?php echo e($lib['footer_tagline'] ?? ''); ?></p>
      <div class="social">
        <?php $ffb = $contact['facebook'] ?? ''; if ($ffb): ?>
        <a href="<?php echo e($ffb); ?>" target="_blank" rel="noopener" aria-label="Facebook">
          <svg viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12Z"/></svg>
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div>
      <h3>Navigation</h3>
      <ul>
        <li><a href="#aikido">L'Aïkido</a></li>
        <li><a href="#dojo">Horaires &amp; tarifs</a></li>
        <li><a href="#professeurs">Professeurs</a></li>
        <li><a href="#galerie">Galerie</a></li>
        <li><a href="#histoire">Notre histoire</a></li>
        <li><a href="#videos">Techniques</a></li>
        <li><a href="#grades">Grades &amp; armes</a></li>
        <li><a href="#glossaire">Glossaire</a></li>
        <li><a href="#bibliographie">Bibliographie</a></li>
        <li><a href="#faq">FAQ</a></li>
        <li><a href="#contact">Cours d'essai</a></li>
      </ul>
    </div>
    <div>
      <h3>Liens utiles</h3>
      <ul>
        <?php foreach ($liens as $l): ?>
        <li><a href="<?php echo e($l['url'] ?? '#'); ?>" target="_blank" rel="noopener"><?php echo e($l['label'] ?? ''); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="foot-bottom">
    © <span id="yr"></span> École d'Aïkido Musubi Dojo · Jurbise · Tous droits réservés ·
    <a href="mentions-legales.php">Mentions légales</a> ·
    <a href="confidentialite.php">Confidentialité</a> ·
    <a href="#accueil">Retour en haut</a>
  </div>
</footer>

<button class="top" id="top" aria-label="Haut de page">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="m6 15 6-6 6 6"/></svg>
</button>
<a class="mobile-cta" id="mcta" href="#contact">
  <svg viewBox="0 0 24 24" fill="none" stroke-width="2"><path d="M12 3v18"/><path d="M3 12h18"/></svg>
  Essai gratuit
</a>

<!-- lightbox -->
<div class="lb" id="lb" role="dialog" aria-modal="true" aria-label="Photo agrandie">
  <button class="lb-close" id="lb-close" aria-label="Fermer">×</button>
  <button class="lb-prev" id="lb-prev" aria-label="Photo précédente">‹</button>
  <img id="lb-img" src="" alt=""/>
  <button class="lb-next" id="lb-next" aria-label="Photo suivante">›</button>
</div>

<script>window.GLOSS = <?php echo json_encode($glossaire, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;</script>
<script>
// header scroll state + back to top
var head=document.getElementById('head'),topBtn=document.getElementById('top'),mcta=document.getElementById('mcta');
addEventListener('scroll',function(){
  var y=scrollY;
  head.classList.toggle('scrolled',y>40);
  topBtn.classList.toggle('show',y>600);
  if(mcta)mcta.classList.toggle('show',y>600);
});
// mobile menu
var burger=document.getElementById('burger'),menu=document.getElementById('menu');
burger.addEventListener('click',function(){
  var open=menu.classList.toggle('open');
  burger.setAttribute('aria-expanded',open?'true':'false');
  burger.setAttribute('aria-label',open?'Fermer le menu':'Ouvrir le menu');
});
menu.querySelectorAll('a').forEach(function(a){a.addEventListener('click',function(){menu.classList.remove('open');burger.setAttribute('aria-expanded','false');})});
topBtn.addEventListener('click',function(){scrollTo({top:0,behavior:'smooth'})});
// reveal on scroll
var io=new IntersectionObserver(function(es){
  es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target)}})
},{threshold:.12,rootMargin:'0px 0px -60px 0px'});
document.querySelectorAll('.reveal').forEach(function(el){io.observe(el)});

// ---------- helpers ----------
function norm(s){return (s||'').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');}

// ---------- bibliographie ----------
var BOOKS=<?php echo json_encode(array_map(fn($b) => ['c' => $b['cat'] ?? '', 'img' => $b['img'] ?? '', 't' => $b['t'] ?? '', 'a' => $b['a'] ?? '', 'e' => $b['e'] ?? ''], $biblio), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
var bgrid=document.getElementById('biblio-grid');
function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function renderBooks(cat){
  bgrid.innerHTML=BOOKS.filter(function(b){return cat==='all'||b.c===cat;}).map(function(b){
    var src=b.img?(b.img.indexOf('/')>-1?b.img:'images/bibliographie/'+b.img):'';
    var cover=src?'<div class="cover"><img src="'+src+'" alt="Couverture : '+esc(b.t)+'" loading="lazy" width="200" height="300"></div>'
                    :'<div class="cover na">Image non disponible</div>';
    return '<div class="book">'+cover+'<b>'+esc(b.t)+'</b><span>'+esc(b.a)+(b.e?' · '+esc(b.e):'')+'</span></div>';
  }).join('');
}
renderBooks('all');
document.querySelectorAll('.biblio-tab').forEach(function(tab){
  tab.addEventListener('click',function(){
    document.querySelectorAll('.biblio-tab').forEach(function(t){t.classList.remove('active')});
    tab.classList.add('active');renderBooks(tab.dataset.cat);
  });
});

// ---------- glossaire ----------
var gbody=document.getElementById('gloss-body'),gcount=document.getElementById('gloss-count'),ginput=document.getElementById('gloss-input');
function renderGloss(q){
  var nq=norm(q);var total=0;
  var openByDefault = false; // toujours replié par défaut (desktop comme mobile) ; s'ouvre à la recherche
  var html=(window.GLOSS||[]).map(function(sec){
    var items=sec.items.filter(function(it){return !nq||norm(it.t).indexOf(nq)>-1||norm(it.p).indexOf(nq)>-1||norm(it.d).indexOf(nq)>-1;});
    if(!items.length)return '';
    total+=items.length;
    var rows=items.map(function(it){
      return '<div class="gloss-row"><div class="gt">'+esc(it.t)+(it.p?' <span class="gp">'+esc(it.p)+'</span>':'')+'</div><div class="gd">'+esc(it.d)+'</div></div>';
    }).join('');
    var open=(nq||openByDefault)?' open':'';
    return '<details class="gloss-sec"'+open+'><summary>'+esc(sec.title)+'<span class="gc">'+items.length+'</span></summary><div class="gloss-list">'+rows+'</div></details>';
  }).join('');
  gbody.innerHTML=html||'<p class="gloss-empty">Aucun terme ne correspond à votre recherche.</p>';
  gcount.textContent=total+' terme'+(total>1?'s':'')+(nq?' trouvé'+(total>1?'s':''):'');
}
if(gbody){renderGloss('');ginput.addEventListener('input',function(){renderGloss(ginput.value)});}

// ---------- faq ----------
document.querySelectorAll('.faq-item').forEach(function(item){
  var q=item.querySelector('.faq-q'),a=item.querySelector('.faq-a');
  q.addEventListener('click',function(){
    var open=item.classList.toggle('open');
    q.setAttribute('aria-expanded',open?'true':'false');
    a.style.maxHeight=open?a.scrollHeight+'px':'0';
  });
});

// ---------- lightbox ----------
var gitems=[].slice.call(document.querySelectorAll('.gal-item')),lb=document.getElementById('lb'),lbimg=document.getElementById('lb-img'),lbi=0;
function openLb(i){lbi=(i+gitems.length)%gitems.length;var b=gitems[lbi];lbimg.src=b.dataset.full;lbimg.alt=b.querySelector('img').alt;lb.classList.add('open');}
gitems.forEach(function(b,i){b.addEventListener('click',function(){openLb(i)})});
document.getElementById('lb-close').addEventListener('click',function(){lb.classList.remove('open')});
document.getElementById('lb-next').addEventListener('click',function(e){e.stopPropagation();openLb(lbi+1)});
document.getElementById('lb-prev').addEventListener('click',function(e){e.stopPropagation();openLb(lbi-1)});
lb.addEventListener('click',function(e){if(e.target===lb)lb.classList.remove('open')});
addEventListener('keydown',function(e){if(!lb.classList.contains('open'))return;if(e.key==='Escape')lb.classList.remove('open');if(e.key==='ArrowRight')openLb(lbi+1);if(e.key==='ArrowLeft')openLb(lbi-1);});

// ---------- scrollspy ----------
var navLinks=[].slice.call(document.querySelectorAll('.menu a[href^="#"]'));
var spy=new IntersectionObserver(function(es){
  es.forEach(function(e){
    if(e.isIntersecting){var id=e.target.id;navLinks.forEach(function(a){a.classList.toggle('active',a.getAttribute('href')==='#'+id);});}
  });
},{rootMargin:'-45% 0px -50% 0px'});
navLinks.forEach(function(a){var el=document.getElementById(a.getAttribute('href').slice(1));if(el)spy.observe(el);});

// ---------- counters ----------
var strip=document.querySelector('.strip'),counted=false;
function runCounters(){
  if(counted)return;counted=true;
  document.querySelectorAll('.strip .stat b').forEach(function(el){
    var t=el.textContent.trim();if(!/^\d+$/.test(t))return;
    var target=parseInt(t,10),start=target>1900?target-45:0,dur=1300,t0=null;
    function step(ts){if(!t0)t0=ts;var p=Math.min((ts-t0)/dur,1),eased=1-Math.pow(1-p,3);el.textContent=Math.floor(start+(target-start)*eased);if(p<1)requestAnimationFrame(step);}
    requestAnimationFrame(step);
  });
}
if(strip&&!matchMedia('(prefers-reduced-motion:reduce)').matches){
  new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting)runCounters();});},{threshold:.4}).observe(strip);
}
</script>
</body>
</html>
