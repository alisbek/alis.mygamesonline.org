<?php
require_once 'includes/header.php';
?>

<section class="section about-page">
    <div class="container">
        <h1 class="section-title"><?= __('about.title') ?></h1>
        
        <div class="about-content" style="margin-bottom:60px;">
            <div class="about-image" style="background:var(--color-bg-alt);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;color:var(--color-text-light);border-radius:var(--radius-lg);overflow:hidden;">
                <img src="https://media.pakamera.net/g/s12775599/1420x0/the-feltee-handcraft-studio.jpg" alt="The Feltee Handcraft Studio" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <div class="about-text">
                <h2>Nasza Historia / Our Story</h2>
                <p>The idea was born from a desire to combine centuries-old nomadic craftsmanship with modern design. We wanted to show the world the beauty of Kyrgyz felting art and create products that bring yurt warmth into contemporary homes.</p>
                <p>At Feltee Handcraft Studio, we find joy in simple, conscious living, choosing natural, sustainable materials. Our products are free from plastics and harmful chemicals, reflecting our commitment to the well-being of both people and the planet.</p>
                <p>Our journey began through collaboration with local artisans in Kyrgyzstan. We wanted to create a bridge between traditional handicraft and the European market, preserving authenticity and quality of execution.</p>
            </div>
        </div>
        
        <div class="about-content">
            <div class="about-text">
                <h2>Nasze Rzemiosło / Our Craft</h2>
                <p>Every Feltee product is made using traditional wet-felting techniques passed down through generations of Kyrgyz nomads. We use 100% natural sheep wool known for its thermoregulatory properties and durability.</p>
                <p>This ancient method creates a seamless, breathable material that naturally regulates temperature - keeping your feet warm in winter and cool in summer. The wool breathes and remains pleasant when it's warm.</p>
                <p>Our seamless felted slippers are our pride. They mold perfectly to the shape of the foot and have no seams, making them particularly comfortable. The entire process, from selecting the wool to the final shaping, can take several days.</p>
            </div>
            <div class="about-image" style="background:var(--color-bg-alt);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;border-radius:var(--radius-lg);overflow:hidden;">
                <img src="https://media.pakamera.net/g/s12775599/1420x0/the-feltee-handcraft-studio_01.jpg" alt="Felting process" style="width:100%;height:100%;object-fit:cover;">
            </div>
        </div>
        
        <div style="text-align:center;margin-top:60px;padding:40px;background:var(--color-white);border-radius:var(--radius-lg);">
            <h2 style="margin-bottom:16px;">Why Choose Feltee?</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:30px;margin-top:30px;">
                <div>
                    <h3 style="color:var(--color-primary);margin-bottom:8px;">100% Natural Wool</h3>
                    <p style="color:var(--color-text-light);">Thermoregulatory properties, breathable and durable</p>
                </div>
                <div>
                    <h3 style="color:var(--color-primary);margin-bottom:8px;">Seamless Design</h3>
                    <p style="color:var(--color-text-light);">Molds to your foot for ultimate comfort</p>
                </div>
                <div>
                    <h3 style="color:var(--color-primary);margin-bottom:8px;">Eco-Friendly</h3>
                    <p style="color:var(--color-text-light);">No plastics, no harmful chemicals</p>
                </div>
                <div>
                    <h3 style="color:var(--color-primary);margin-bottom:8px;">Handcrafted</h3>
                    <p style="color:var(--color-text-light);">Traditional Kyrgyz nomadic techniques</p>
                </div>
            </div>
        </div>
        
        <div style="margin-top:60px;padding:40px;background:var(--color-white);border-radius:var(--radius-lg);">
            <h2 style="text-align:center;margin-bottom:30px;">3 Things That Define Us</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:40px;">
                <div style="text-align:center;">
                    <div style="font-size:3rem;margin-bottom:16px;">🧦</div>
                    <h3 style="color:var(--color-primary);margin-bottom:12px;">Seamless Slippers</h3>
                    <p style="color:var(--color-text-light);">Our felted slippers have no seams, molding perfectly to your feet for unparalleled comfort.</p>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:3rem;margin-bottom:16px;">🌿</div>
                    <h3 style="color:var(--color-primary);margin-bottom:12px;">Natural Materials</h3>
                    <p style="color:var(--color-text-light);">Only 100% sheep wool - warm in winter, breathable in summer, completely natural.</p>
                </div>
                <div style="text-align:center;">
                    <div style="font-size:3rem;margin-bottom:16px;">🏔️</div>
                    <h3 style="color:var(--color-primary);margin-bottom:12px;">Kyrgyz Heritage</h3>
                    <p style="color:var(--color-text-light);">Centuries of nomadic tradition in every product, handmade by skilled artisans.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>