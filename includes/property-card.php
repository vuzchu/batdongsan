<?php
/**
 * Partial hien thi 1 the bat dong san. Yeu cau bien $p (mang du lieu property) da duoc set truoc khi include.
 */
$badgeClass = $p['transaction_type'] === 'rent' ? 'badge-rent' : 'badge-sale';
$badgeText = $p['transaction_type'] === 'rent' ? 'Cho thue' : 'Ban';
$image = !empty($p['image']) ? BASE_URL . '/' . $p['image'] : BASE_URL . '/assets/images/placeholder.svg';
?>
<article class="property-card">
  <a href="<?= BASE_URL ?>/property-detail.php?slug=<?= urlencode($p['slug']) ?>" class="property-thumb">
    <span class="badge <?= $badgeClass ?>"><?= e($badgeText) ?></span>
    <img src="<?= e($image) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
  </a>
  <div class="property-body">
    <div class="property-price"><?= e(formatPrice((float)$p['price'], $p['price_unit'])) ?></div>
    <h3 class="property-title"><a href="<?= BASE_URL ?>/property-detail.php?slug=<?= urlencode($p['slug']) ?>"><?= e($p['title']) ?></a></h3>
    <p class="property-location">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <?= e($p['district_name'] ?? '') ?>, <?= e($p['city_name'] ?? '') ?>
    </p>
    <div class="property-specs">
      <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v13h18V7"/><path d="M3 7l9-4 9 4"/></svg> <?= (int)$p['bedrooms'] ?> Phong ngu</span>
      <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg> <?= (int)$p['bathrooms'] ?> Phong tam</span>
      <span><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg> <?= e(rtrim(rtrim(number_format((float)$p['area'], 1), '0'), '.')) ?> m2</span>
    </div>
  </div>
</article>
