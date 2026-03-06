<?php
require_once 'includes/header.php';
?>

<section class="section about-page">
    <div class="container">
        <h1 class="section-title"><?= __('about.title') ?></h1>
        
        <div class="about-content" style="margin-bottom:60px;">
            <div class="about-image" style="background:var(--color-bg-alt);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;color:var(--color-text-light);border-radius:var(--radius-lg);overflow:hidden;">
                <img src="<?= SITE_URL ?>/uploads/products/feltee-studio.jpg" alt="The Feltee Handcraft Studio" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="about-text">
                <h2><?= __('about.story') ?></h2>
                <p><?= __('about.story_text1') ?></p>
                <p><?= __('about.story_text2') ?></p>
                <p><?= __('about.story_text3') ?></p>
            </div>
        </div>
        
        <div class="about-content">
            <div class="about-text">
                <h2><?= __('about.craft') ?></h2>
                <p><?= __('about.craft_text1') ?></p>
                <p><?= __('about.craft_text2') ?></p>
                <p><?= __('about.craft_text3') ?></p>
            </div>
            <div class="about-image" style="background:var(--color-bg-alt);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;border-radius:var(--radius-lg);overflow:hidden;">
                <img src="<?= SITE_URL ?>/uploads/products/feltee-studio-01.jpg" alt="<?= __('about.craft') ?>" style="width:100%;height:100%;object-fit:cover;">
            </div>
        </div>
        
        <div style="text-align:center;margin-top:60px;padding:40px;background:var(--color-white);border-radius:var(--radius-lg);">
            <h2 style="margin-bottom:16px;"><?= __('about.why_title') ?></h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:30px;margin-top:30px;">
                <div>
                    <h3 style="color:var(--color-primary);margin-bottom:8px;"><?= __('about.feature_natural_title') ?></h3>
                    <p style="color:var(--color-text-light);"><?= __('about.feature_natural_text') ?></p>
                </div>
                <div>
                    <h3 style="color:var(--color-primary);margin-bottom:8px;"><?= __('about.feature_seamless_title') ?></h3>
                    <p style="color:var(--color-text-light);"><?= __('about.feature_seamless_text') ?></p>
                </div>
                <div>
                    <h3 style="color:var(--color-primary);margin-bottom:8px;"><?= __('about.feature_eco_title') ?></h3>
                    <p style="color:var(--color-text-light);"><?= __('about.feature_eco_text') ?></p>
                </div>
                <div>
                    <h3 style="color:var(--color-primary);margin-bottom:8px;"><?= __('about.feature_handcrafted_title') ?></h3>
                    <p style="color:var(--color-text-light);"><?= __('about.feature_handcrafted_text') ?></p>
                </div>
            </div>
        </div>
        
        <div style="margin-top:60px;padding:40px;background:var(--color-white);border-radius:var(--radius-lg);">
            <h2 style="text-align:center;margin-bottom:30px;"><?= __('about.define_title') ?></h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:40px;">
                <div style="text-align:center;">
                    <div style="font-size:3rem;margin-bottom:16px;">&#x1f9e6;</div>
                    <h3 style="color:var(--color-primary);margin-bottom:12px;"><?= __('about.define_seamless_title') ?></h3>
                    <p style="color:var(--color-text-light);"><?= __('about.define_seamless_text') ?></p>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:3rem;margin-bottom:16px;">&#x1f33f;</div>
                    <h3 style="color:var(--color-primary);margin-bottom:12px;"><?= __('about.define_natural_title') ?></h3>
                    <p style="color:var(--color-text-light);"><?= __('about.define_natural_text') ?></p>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:3rem;margin-bottom:16px;">&#x1f3d4;&#xfe0f;</div>
                    <h3 style="color:var(--color-primary);margin-bottom:12px;"><?= __('about.define_heritage_title') ?></h3>
                    <p style="color:var(--color-text-light);"><?= __('about.define_heritage_text') ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
