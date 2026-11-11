<?php
require_once 'config/db.php';
require_once 'config/config.php';

$services = $conn->query("
    SELECT id, name, icon FROM services WHERE status='active' ORDER BY sort_order ASC, id ASC
")->fetch_all(MYSQLI_ASSOC);

$where   = ["u.role = 'provider'", "u.status = 'active'"];
$params  = [];
$types   = '';

$filter_service      = (int)($_GET['service']  ?? 0);
$filter_district     = sanitize($_GET['district'] ?? '');
$filter_search       = sanitize($_GET['q'] ?? '');
$filter_experience   = sanitize($_GET['experience'] ?? '');
$filter_availability = sanitize($_GET['availability'] ?? '');

if ($filter_service) {
    $where[]  = "ja.service_id = ?";
    $params[] = $filter_service;
    $types   .= 'i';
}
if ($filter_district) {
    $where[]  = "ja.district = ?";
    $params[] = $filter_district;
    $types   .= 's';
}
if ($filter_search) {
    $where[]  = "(u.name LIKE ? OR ja.full_name LIKE ? OR ja.bio LIKE ?)";
    $like = '%' . $filter_search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= 'sss';
}
if ($filter_availability) {
    $where[]  = "ja.availability LIKE ?";
    $params[] = '%' . str_replace('-', ' ', $filter_availability) . '%';
    $types   .= 's';
}
if ($filter_experience) {
    $exp_map = [
        '0-1' => ['Less than 1 year', '0', '1 year'],
        '1-3' => ['1-2 years', '1-2', '2 year'],
        '3-5' => ['3-5 years', '3-5', '3 year', '4 year'],
        '5+'  => ['5+ years', '5+', '5 year', '6 year', '7 year', '8 year', '9 year', '10'],
    ];
    if (!empty($exp_map[$filter_experience])) {
        $ors = [];
        foreach ($exp_map[$filter_experience] as $piece) {
            $ors[] = 'ja.experience LIKE ?';
            $params[] = '%' . $piece . '%';
            $types   .= 's';
        }
        $where[] = '(' . implode(' OR ', $ors) . ')';
    }
}

$where_sql = implode(' AND ', $where);

$sql = "
    SELECT u.id, u.name, u.created_at, u.photo, u.profile_photo,
           s.name AS service_name, s.icon AS service_icon,
           ja.status AS app_status, ja.full_name, ja.age, ja.location, ja.district,
           ja.experience, ja.bio, ja.availability, ja.expected_pay, ja.languages,
           ja.created_at AS applied_at
    FROM users u
    LEFT JOIN job_applications ja ON ja.provider_id = u.id AND ja.status = 'approved'
    LEFT JOIN services s ON s.id = ja.service_id
    WHERE $where_sql
    GROUP BY u.id
    ORDER BY u.created_at DESC
";

if ($params) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $workers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $workers = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
}

function worker_photo_url(array $w): string {
    $file = trim((string)($w['photo'] ?: ($w['profile_photo'] ?? '')));
    if ($file === '') return 'Image/profile-placeholder.png';
    if (preg_match('#^https?://#i', $file)) return $file;
    if (strpos($file, 'uploads/') === 0 || strpos($file, 'Image/') === 0) return $file;
    return 'uploads/avatars/' . ltrim($file, '/');
}

function worker_parse_bio(?string $bio): array {
    $raw = html_entity_decode((string)$bio, ENT_QUOTES, 'UTF-8');
    $education = '';
    $about = '';
    if (preg_match('/EDUCATION:\s*([^\n\r]+)/i', $raw, $m)) {
        $education = trim($m[1]);
    }
    if (preg_match('/ABOUT:\s*(.+)$/is', $raw, $m)) {
        $about = trim($m[1]);
    } else {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];
        $keep = [];
        foreach ($lines as $line) {
            if (preg_match('/^(FULL NAME|PHONE|EMAIL|DOB|DOCS|EDUCATION|ABOUT)\s*:/i', $line)) continue;
            $keep[] = $line;
        }
        $about = trim(implode("\n", $keep));
    }
    return ['education' => $education, 'about' => $about];
}

function worker_exp_label(?string $exp): string {
    $exp = trim((string)$exp);
    if ($exp === '') return 'Experience on request';
    if (stripos($exp, 'year') !== false) {
        return $exp . (stripos($exp, 'experience') === false ? ' experience' : '');
    }
    return $exp . ' years experience';
}

$total = count($workers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<?php $seo_keywords = 'find a worker Rwanda, hire nanny Kigali, hire housekeeper Rwanda, hire home chef Kigali, verified domestic workers Rwanda, Home Help find worker'; ?>
<?php include __DIR__ . '/includes/seo-meta.php'; ?>
<meta name="description" content="Find trusted domestic workers in Rwanda. Browse verified nannies, housekeepers, chefs and caregivers on Home Help."/>
<title>Find a Worker | Home Help Rwanda</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<script src="https://unpkg.com/lucide@0.468.0/dist/umd/lucide.min.js"></script>
<link rel="stylesheet" href="<?= rtrim(BASE_URL,'/') ?>/assets/css/public.css"/>
</head>
<body>

<?php $nav_active = 'find-worker'; include __DIR__ . '/includes/public-header.php'; ?>

<section class="page-hero">
  <div class="container">
    <div class="page-eyebrow"><?= (int)$total ?> worker<?= $total!=1?'s':'' ?> available</div>
    <h1 class="page-title">Find Trusted Domestic Workers</h1>
    <p class="page-sub">Browse verified nannies, housekeepers, chefs, and caregivers available in Rwanda. Book directly through your dashboard.</p>
  </div>
</section>

<section class="main">
  <div class="container">
    <div class="filter-chips">
      <a href="/find-worker" class="chip <?= !$filter_service?'active':'' ?>">All Workers</a>
      <?php foreach ($services as $svc): ?>
        <a href="/find-worker?service=<?= (int)$svc['id'] ?>" class="chip <?= $filter_service==$svc['id']?'active':'' ?>">
          <?= htmlspecialchars($svc['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="results-header">
      <div>
        <h2 class="results-title">Featured workers</h2>
        <p class="results-count">Showing <strong><?= (int)$total ?></strong> verified worker<?= $total!=1?'s':'' ?>
          <?= $filter_search ? ' for "'.e($filter_search).'"' : '' ?>
          <?= $filter_service ? ' in selected category' : '' ?>
        </p>
      </div>
      <?php if ($filter_search || $filter_service || $filter_district): ?>
        <a href="/find-worker" class="clear-filters">Clear filters</a>
      <?php endif; ?>
    </div>

    <div class="workers-grid">
      <?php if (empty($workers)): ?>
        <div class="empty-state reveal">
          <i data-lucide="search-x" style="width:36px;height:36px;color:var(--soft)"></i>
          <h3>No Workers Found</h3>
          <p>No workers match your search right now. Try different filters or check back soon.</p>
          <a href="/find-worker">Clear Filters</a>
        </div>
      <?php else: ?>
        <?php foreach ($workers as $w):
          $display_name = trim((string)($w['full_name'] ?? '')) ?: $w['name'];
          $svc_name = $w['service_name'] ?: 'Domestic Worker';
          $exp_label = worker_exp_label($w['experience'] ?? '');
          $parsed = worker_parse_bio($w['bio'] ?? '');
          $photo = worker_photo_url($w);
          $place_parts = array_values(array_unique(array_filter([
            trim((string)($w['location'] ?? '')),
            trim((string)($w['district'] ?? '')),
          ])));
          $place = implode(' · ', $place_parts);
          $joined = !empty($w['created_at']) ? date('M Y', strtotime($w['created_at'])) : '';
        ?>
          <?php
            $meta_bits = [$exp_label];
            if (!empty($w['age'])) $meta_bits[] = (int)$w['age'] . ' yrs';
            if ($place !== '') $meta_bits[] = $place;
            if (!empty($w['availability'])) $meta_bits[] = $w['availability'];
            if (!empty($w['languages'])) $meta_bits[] = $w['languages'];
            if ($parsed['education'] !== '') $meta_bits[] = $parsed['education'];
            if (!empty($w['expected_pay'])) $meta_bits[] = $w['expected_pay'];
            if ($joined !== '') $meta_bits[] = 'Since ' . $joined;
            $meta_line = implode(' · ', $meta_bits);
          ?>
          <article class="worker-card reveal">
            <div class="worker-media">
              <img src="<?= e($photo) ?>" alt="<?= e($display_name) ?>"/>
            </div>
            <div class="worker-body">
              <span class="worker-badge">Verified</span>
              <div class="worker-name"><?= e($display_name) ?></div>
              <div class="worker-role"><?= e($svc_name) ?></div>
              <div class="worker-meta" title="<?= e($parsed['about'] !== '' ? $parsed['about'] : $meta_line) ?>"><?= e($meta_line) ?></div>
              <a href="/login" class="worker-btn" data-open-login>Hire Now</a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="cta-wrap">
      <div class="cta-card reveal">
        <div class="cta-inner">
          <h2>Can't Find the Right Worker?</h2>
          <p>Submit a booking request and our agents will find the perfect match for your needs across Rwanda.</p>
          <div class="cta-btns">
            <a href="/register" class="cta-btn-primary" data-open-book>Post a Request <i data-lucide="arrow-right"></i></a>
            <a href="/#contact" class="cta-btn-ghost">Contact Our Team</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="/" class="footer-brand">
          <img src="Image/icon/homehelp_logo_transparent_white.svg" alt="Home Help"/>
          <span>HomeHelp</span>
        </a>
        <p class="footer-desc">Rwanda&#8217;s trusted marketplace for domestic services, connecting households with verified workers across the country.</p>
        <div class="social-links">
          <a class="social-link" href="https://www.facebook.com/" target="_blank" rel="noopener" aria-label="Facebook">
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M14 13.5h2.5l1-4H14v-2c0-1.03 0-2 2-2h1.5V2.14C17.026 2.087 15.824 2 14.647 2 11.928 2 10 3.657 10 6.7v2.8H7v4h3V22h4z"/></svg>
          </a>
          <a class="social-link" href="https://www.instagram.com/" target="_blank" rel="noopener" aria-label="Instagram">
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2m-.2 2A3.6 3.6 0 0 0 4 7.6v8.8C4 18.39 5.61 20 7.6 20h8.8a3.6 3.6 0 0 0 3.6-3.6V7.6C20 5.61 18.39 4 16.4 4H7.6m9.65 1.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10m0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/></svg>
          </a>
          <a class="social-link" href="https://x.com/" target="_blank" rel="noopener" aria-label="X">
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.743l7.727-8.835L1.254 2.25H8.08l4.264 5.686L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>
          </a>
          <a class="social-link" href="https://wa.me/250790533144" target="_blank" rel="noopener" aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" aria-hidden="true" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          </a>
        </div>
      </div>
      <div class="footer-col">
        <h5>Services</h5>
        <ul class="footer-links">
          <li><a href="/home-cleaning">Home Cleaning Kigali</a></li>
          <li><a href="/housekeeping">Housekeeping</a></li>
          <li><a href="/home-chef">Home Chef</a></li>
          <li><a href="/nanny">Nanny &amp; Caregiver</a></li>
          <li><a href="/office-cleaning">Office Cleaning</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Platform</h5>
        <ul class="footer-links">
          <li><a href="/#services">Why Choose Us</a></li>
          <li><a href="/#how-it-works">How It Works</a></li>
          <li><a href="/career">Career</a></li>
          <li><a href="/#faq">FAQ</a></li>
          <li><a href="/#contact">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Account</h5>
        <ul class="footer-links">
          <li><a href="/worker-login" data-open-worker-login>Login</a></li>
          <li><a href="/worker-register" data-open-worker-register>Register</a></li>
          <li><a href="/login" data-open-login>Hire Login</a></li>
          <li><a href="/register" data-open-register>Hire Register</a></li>
          <li><a href="/admin-login">Admin Login</a></li>
          <li><a href="/staff-login">Team Login</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> Home Help &middot; Rwanda</p>
      <div class="footer-bottom-links">
        <a href="/privacy">Privacy Policy</a>
        <a href="/terms">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>

<script>
const observer=new IntersectionObserver(function(entries){
  entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('visible');observer.unobserve(e.target);}});
},{threshold:0.1});
document.querySelectorAll('.reveal').forEach(function(el){observer.observe(el);});
if(window.lucide){lucide.createIcons();}
</script>
<?php
include __DIR__ . '/includes/worker-login-modal.php';
include __DIR__ . '/includes/worker-register-modal.php';
include __DIR__ . '/includes/client-login-modal.php';
include __DIR__ . '/includes/register-modal.php';
include __DIR__ . '/includes/book-modal.php';
?>
</body>
</html>
