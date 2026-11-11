<?php
require_once 'config/db.php';
require_once 'config/config.php';

$services = $conn->query("
    SELECT id, name, description, icon
    FROM services
    WHERE status = 'active'
    ORDER BY sort_order ASC, id ASC
")->fetch_all(MYSQLI_ASSOC);

$total_agents = $conn->query("SELECT COUNT(*) FROM users WHERE role='agent'")->fetch_row()[0];

function career_icon(string $name): string {
    $n = strtolower($name);
    $map = [
        ['nanny','baby'], ['babysit','baby'], ['child','baby'],
        ['chef','chef-hat'], ['cook','chef-hat'], ['kitchen','utensils'],
        ['care','heart-handshake'], ['elder','heart-handshake'], ['nurse','heart-handshake'],
        ['pet','paw-print'], ['groom','paw-print'],
        ['garden','flower-2'], ['laundry','shirt'], ['driver','car'],
        ['office','building-2'], ['clean','sparkles'], ['house','house'], ['maid','sparkles'],
    ];
    foreach ($map as [$kw, $icon]) {
        if (strpos($n, $kw) !== false) return $icon;
    }
    return 'briefcase';
}

function career_image(string $name): string {
    $n = strtolower($name);
    if (strpos($n, 'nanny') !== false || strpos($n, 'child') !== false || strpos($n, 'babysit') !== false) return 'Image/service-nanny.jpg';
    if (strpos($n, 'chef') !== false || strpos($n, 'cook') !== false) return 'Image/service-chef.jpg';
    if (strpos($n, 'care') !== false) return 'Image/service-caregiver.jpg';
    if (strpos($n, 'laundry') !== false || strpos($n, 'iron') !== false) return 'Image/service-laundry.jpg';
    if (strpos($n, 'garden') !== false) return 'Image/service-gardener.jpg';
    if (strpos($n, 'clean') !== false) return 'Image/service-cleaning.jpg';
    if (strpos($n, 'house') !== false || strpos($n, 'maid') !== false) return 'Image/service-housekeeping.jpg';
    return 'Image/service-housekeeping.jpg';
}

$job_cards = [];
if (!empty($services)) {
    foreach ($services as $svc) {
        $job_cards[] = [
            'id'   => (int)$svc['id'],
            'name' => $svc['name'],
            'desc' => $svc['description'] ?: 'Professional domestic work opportunity available across Rwanda.',
            'icon' => career_icon($svc['name']),
            'img'  => career_image($svc['name']),
        ];
    }
} else {
    $job_cards = [
        ['id' => 0, 'name' => 'Nanny / Babysitter', 'desc' => 'Care for children in a loving household environment.', 'icon' => 'baby', 'img' => 'Image/service-nanny.jpg'],
        ['id' => 0, 'name' => 'Housekeeper / Cleaner', 'desc' => 'Keep homes clean, tidy, and organized.', 'icon' => 'sparkles', 'img' => 'Image/service-cleaning.jpg'],
        ['id' => 0, 'name' => 'Home Chef / Cook', 'desc' => 'Prepare daily meals and special event catering.', 'icon' => 'chef-hat', 'img' => 'Image/service-chef.jpg'],
        ['id' => 0, 'name' => 'Caregiver', 'desc' => 'Support elderly or patients with daily needs.', 'icon' => 'heart-handshake', 'img' => 'Image/service-caregiver.jpg'],
    ];
}

$nav_active = 'career';
$base = rtrim(BASE_URL, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php $seo_keywords = 'domestic jobs Rwanda, find domestic work Kigali, nanny jobs Rwanda, housekeeping jobs Kigali, chef jobs Rwanda, caregiver jobs Rwanda, Home Help careers, apply for domestic work Rwanda'; ?>
<?php include __DIR__ . '/includes/seo-meta.php'; ?>
<meta name="description" content="Find domestic work in Rwanda. Join Home Help and connect with verified households looking for nannies, housekeepers, chefs, and caregivers."/>
<title>Career | Find Domestic Work in Rwanda — Home Help</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?= $base ?>/assets/css/public.css">
<style>
.cr-step-label{display:block;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:6px}
.cr-step h3{font-size:1.05rem;font-weight:750;color:#fff;margin:0 0 6px}
.cr-step p{margin:0;font-size:.92rem;line-height:1.55;color:rgba(255,255,255,.68)}
.cr-step:hover{background:rgba(255,255,255,.14)}
.cr-split{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:stretch}
.cr-benefits{display:flex;flex-direction:column;gap:14px}
.cr-benefit{display:flex;gap:14px;align-items:flex-start;padding:18px;border:none;border-radius:16px;background:rgba(255,255,255,.08);backdrop-filter:blur(18px) saturate(140%);-webkit-backdrop-filter:blur(18px) saturate(140%);box-shadow:none;text-align:left}
.cr-benefit-icon{width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.12);color:#fff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;border:none}
.cr-benefit-icon svg{width:18px;height:18px}
.cr-benefit h3{font-size:.95rem;font-weight:750;color:#fff;margin:0 0 4px}
.cr-benefit p{font-size:.86rem;color:rgba(255,255,255,.68);line-height:1.6;margin:0}
.cr-req{background:rgba(255,255,255,.08);border:none;border-radius:22px;padding:28px;display:flex;flex-direction:column;text-align:left;align-items:stretch;backdrop-filter:blur(22px) saturate(140%);-webkit-backdrop-filter:blur(22px) saturate(140%);box-shadow:none}
.cr-req h3{font-size:1.2rem;font-weight:800;color:#fff;margin:0 0 4px}
.cr-req-sub{font-size:.86rem;color:rgba(255,255,255,.55);margin:0 0 18px}
.cr-req-list{display:flex;flex-direction:column;gap:12px;margin-bottom:18px;width:100%;align-items:stretch}
.cr-req-item{display:flex;align-items:center;justify-content:flex-start;gap:10px;font-size:.9rem;color:rgba(255,255,255,.82);text-align:left}
.cr-req-item svg{width:18px;height:18px;color:#6EE7B7;flex-shrink:0}
.cr-req-note{font-size:.8rem;color:rgba(255,255,255,.55);line-height:1.6;padding-top:16px;border-top:1px solid rgba(255,255,255,.1);margin-bottom:18px;width:100%;text-align:left}
.cr-req .cr-btn{width:100%;margin-top:auto}
.cr-quotes{display:grid;grid-template-columns:repeat(3,1fr);gap:18px}
.cr-quote{background:rgba(255,255,255,.08);border:none;border-radius:20px;padding:26px;display:flex;flex-direction:column;align-items:stretch;min-height:100%;text-align:left;backdrop-filter:blur(22px) saturate(140%);-webkit-backdrop-filter:blur(22px) saturate(140%);box-shadow:none}
.cr-quote p{font-size:.94rem;color:rgba(255,255,255,.68);line-height:1.7;margin:0 0 22px;flex:1}
.cr-quote-foot{display:flex;flex-direction:row;align-items:center;justify-content:space-between;gap:12px;margin-top:auto}
.cr-quote-author{display:flex;flex-direction:row;align-items:center;gap:12px;min-width:0}
.cr-avatar{width:42px;height:42px;border-radius:50%;background:rgba(255,255,255,.14);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;flex-shrink:0;border:none;box-shadow:none}
.cr-quote-author h5{font-size:.9rem;font-weight:700;color:#fff;margin:0}
.cr-quote-author span{font-size:.76rem;color:rgba(255,255,255,.55)}
.cr-stars{color:#F5B942;font-size:.85rem;letter-spacing:1px;flex-shrink:0}
.cr-cta{padding:40px 0 96px;background:transparent}
.cr-cta-card{background:rgba(255,255,255,.08);border:none;border-radius:28px;padding:48px;display:flex;align-items:center;justify-content:space-between;gap:28px;flex-wrap:wrap;backdrop-filter:blur(22px) saturate(140%);-webkit-backdrop-filter:blur(22px) saturate(140%);box-shadow:none}
.cr-cta-card h2{font-size:clamp(1.5rem,3vw,2.1rem);font-weight:800;color:#fff;letter-spacing:-.02em;margin:0 0 8px}
.cr-cta-card p{color:rgba(255,255,255,.68);max-width:460px;margin:0;line-height:1.65}
.cr-cta-actions{display:flex;flex-wrap:wrap;gap:12px}
@media(max-width:1024px){
  .cr-job{width:min(100%,340px)}
  .cr-hiw,.cr-split{grid-template-columns:1fr;gap:28px}
  .cr-hiw-media{min-height:280px;aspect-ratio:16/10}
  .cr-hiw-media img{position:absolute}
  .cr-section{padding:64px 0}
  .cr-quotes{grid-template-columns:repeat(2,1fr)}
}
@media(max-width:768px){
  .cr-quotes{grid-template-columns:1fr}
  .cr-quote{padding:20px}
  .cr-req{padding:22px 18px}
  .cr-cta{padding:24px 0 64px}
  .cr-cta-card{padding:28px 20px;border-radius:20px;flex-direction:column;align-items:stretch;text-align:center}
  .cr-cta-card p{max-width:none}
  .cr-cta-actions{justify-content:center;width:100%}
  .cr-cta-actions .cr-btn{flex:1 1 auto;min-width:140px}
}
@media(max-width:480px){
  .cr-cta-actions{flex-direction:column}
  .cr-cta-actions .cr-btn{width:100%}
  .cr-benefit{padding:14px}
}
</style>
</head>
<body>

<?php include __DIR__ . '/includes/public-header.php'; ?>

<!-- HERO -->
<section class="cr-hero">
  <div class="container">
    <div class="cr-hero-copy reveal">
      <h1 class="cr-title">Find Domestic Work<br/>in Rwanda.</h1>
      <p class="cr-lead">Join Home Help and connect with verified households looking for nannies, housekeepers, chefs, and caregivers. Free to register.</p>
    </div>
  </div>
</section>

<!-- JOB CATEGORIES -->
<section class="cr-section soft" id="jobs">
  <div class="container">
    <div class="cr-head reveal">
      <h2>Choose your job category</h2>
      <p>Browse open domestic roles and apply for the work that fits your skills and schedule.</p>
    </div>
    <div class="cr-jobs">
      <?php foreach ($job_cards as $i => $job):
        $href = $job['id'] ? $base . '/worker-register?service=' . $job['id'] : $base . '/worker-register';
      ?>
      <a class="cr-job reveal<?= $i % 4 === 0 ? '' : '' ?>" href="<?= htmlspecialchars($href) ?>" data-open-worker-register>
        <div class="cr-job-media">
          <img src="<?= $base ?>/<?= htmlspecialchars($job['img']) ?>" alt="<?= htmlspecialchars($job['name']) ?>"/>
        </div>
        <div class="cr-job-body">
          <h3><?= htmlspecialchars($job['name']) ?></h3>
          <p><?= htmlspecialchars($job['desc']) ?></p>
          <span class="cr-job-cta">Apply Now</span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="cr-section" id="how-it-works">
  <div class="container">
    <div class="cr-head reveal">
      <h2>How it works</h2>
      <p>From registration to your first day at work — our team guides you through every step.</p>
    </div>
    <div class="cr-hiw reveal">
      <div class="cr-hiw-media">
        <img src="<?= $base ?>/Image/cta-step-matched.jpg" alt="Home Help team matching workers with households"/>
        <div class="cr-hiw-stat">
          <div>
            <strong><?= $total_agents ?: '10' ?>+</strong>
            <span>Active agents supporting you</span>
          </div>
          <div class="cr-hiw-stat-icon" aria-hidden="true"><i data-lucide="badge-check"></i></div>
        </div>
      </div>
      <div class="cr-steps">
        <article class="cr-step">
          <span class="cr-step-label">Step 1</span>
          <h3>Create your account</h3>
          <p>Register as a Job Seeker with your name, email, and password. It is completely free.</p>
        </article>
        <article class="cr-step">
          <span class="cr-step-label">Step 2</span>
          <h3>Apply for a role</h3>
          <p>Choose the service you want to provide, then add your experience, availability, and location.</p>
        </article>
        <article class="cr-step">
          <span class="cr-step-label">Step 3</span>
          <h3>Team reviews you</h3>
          <p>Our team reviews your application, verifies your details, and contacts you on WhatsApp.</p>
        </article>
        <article class="cr-step">
          <span class="cr-step-label">Step 4</span>
          <h3>Start working</h3>
          <p>Get matched with a verified household, agree on terms, and begin your new job.</p>
        </article>
      </div>
    </div>
  </div>
</section>

<!-- BENEFITS + REQUIREMENTS -->
<section class="cr-section soft" id="benefits">
  <div class="container">
    <div class="cr-split">
      <div class="reveal">
        <div class="cr-head" style="margin:0 0 24px;text-align:left;max-width:none">
          <h2>Why join Home Help</h2>
          <p>We do not just find you a job — we support you through the process and beyond.</p>
        </div>
        <div class="cr-benefits">
          <div class="cr-benefit">
            <div class="cr-benefit-icon" aria-hidden="true"><i data-lucide="wallet"></i></div>
            <div>
              <h3>Free to register &amp; apply</h3>
              <p>No fees. Creating your profile and applying for jobs costs you nothing.</p>
            </div>
          </div>
          <div class="cr-benefit">
            <div class="cr-benefit-icon" aria-hidden="true"><i data-lucide="shield-check"></i></div>
            <div>
              <h3>Verified households only</h3>
              <p>Every client household is reviewed by our team before you are matched.</p>
            </div>
          </div>
          <div class="cr-benefit">
            <div class="cr-benefit-icon" aria-hidden="true"><i data-lucide="message-circle"></i></div>
            <div>
              <h3>WhatsApp updates</h3>
              <p>Get real-time updates on your application status directly on WhatsApp.</p>
            </div>
          </div>
          <div class="cr-benefit">
            <div class="cr-benefit-icon" aria-hidden="true"><i data-lucide="target"></i></div>
            <div>
              <h3>Dedicated team support</h3>
              <p>A real person reviews your file and helps you through hiring step by step.</p>
            </div>
          </div>
        </div>
      </div>

      <aside class="cr-req reveal">
        <h3>What you need to apply</h3>
        <p class="cr-req-sub">Basic requirements for all positions</p>
        <div class="cr-req-list">
          <div class="cr-req-item"><i data-lucide="circle-check"></i>Rwandan National ID (Indangamuntu)</div>
          <div class="cr-req-item"><i data-lucide="circle-check"></i>Valid phone number for WhatsApp</div>
          <div class="cr-req-item"><i data-lucide="circle-check"></i>At least 1 year of relevant work experience</div>
          <div class="cr-req-item"><i data-lucide="circle-check"></i>Good references from previous employers</div>
          <div class="cr-req-item"><i data-lucide="circle-check"></i>Willingness to undergo background check</div>
          <div class="cr-req-item"><i data-lucide="circle-check"></i>Clean conduct record</div>
        </div>
        <p class="cr-req-note">Don&#8217;t meet all requirements? Apply anyway — our team will guide you on what to prepare.</p>
        <a href="<?= $base ?>/worker-register" class="cr-btn cr-btn-primary" data-open-worker-register>Start My Application <i data-lucide="arrow-right"></i></a>
      </aside>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="cr-section">
  <div class="container">
    <div class="cr-head reveal">
      <h2>Workers who found jobs through us</h2>
      <p>Real stories from people who built steady work with Home Help.</p>
    </div>
    <div class="cr-quotes">
      <article class="cr-quote reveal">
        <p>&ldquo;After months of struggling to find work, I applied on Home Help and got placed with a great family in less than a week. The team was very helpful and patient.&rdquo;</p>
        <div class="cr-quote-foot">
          <div class="cr-quote-author">
            <div class="cr-avatar">J</div>
            <div>
              <h5>Jean Claude Habimana</h5>
              <span>Housekeeper · Musanze</span>
            </div>
          </div>
          <div class="cr-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        </div>
      </article>
      <article class="cr-quote reveal">
        <p>&ldquo;I was nervous about registering online but the process was so simple. Within 3 days a team member called me on WhatsApp and helped me find a nanny job in Kigali.&rdquo;</p>
        <div class="cr-quote-foot">
          <div class="cr-quote-author">
            <div class="cr-avatar">M</div>
            <div>
              <h5>Marie Uwimana</h5>
              <span>Nanny · Kigali, Gasabo</span>
            </div>
          </div>
          <div class="cr-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        </div>
      </article>
      <article class="cr-quote reveal">
        <p>&ldquo;I have been cooking for families for years but never had a formal way to find clients. Home Help changed that completely. Now I have steady work every month.&rdquo;</p>
        <div class="cr-quote-foot">
          <div class="cr-quote-author">
            <div class="cr-avatar">P</div>
            <div>
              <h5>Patrick Nzeyimana</h5>
              <span>Home Chef · Kigali, Kicukiro</span>
            </div>
          </div>
          <div class="cr-stars" aria-label="5 out of 5 stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cr-cta">
  <div class="container">
    <div class="cr-cta-card reveal">
      <div>
        <h2>Ready to start your new career?</h2>
        <p>Create your free profile in minutes and let our team connect you with verified households.</p>
      </div>
      <div class="cr-cta-actions">
        <a href="<?= $base ?>/worker-register" class="cr-btn cr-btn-primary" data-open-worker-register>Apply for a Job <i data-lucide="arrow-right"></i></a>
        <a href="<?= $base ?>/worker-login" class="cr-btn cr-btn-ghost" data-open-worker-login>Log in</a>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/public-footer.php'; ?>
<script>
document.querySelectorAll('a[href^="#"]').forEach(function(link){
  link.addEventListener('click', function(e){
    var target = document.querySelector(link.getAttribute('href'));
    if(target){ e.preventDefault(); target.scrollIntoView({behavior:'smooth', block:'start'}); }
  });
});
var observer = new IntersectionObserver(function(entries){
  entries.forEach(function(e){
    if(e.isIntersecting){ e.target.classList.add('visible'); observer.unobserve(e.target); }
  });
},{threshold:0.12});
document.querySelectorAll('.reveal').forEach(function(el){ observer.observe(el); });
if(window.lucide) lucide.createIcons();
</script>
</body>
</html>
