<?php
require_once 'includes/header.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $subject = "Contact Form - Feltee";
        $body = "Name: $name\nEmail: $email\n\nMessage:\n$message";
        $headers = "From: noreply@alis.mygamesonline.org\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        if (mail('support@feltee.kg', $subject, $body, $headers)) {
            $success = true;
        } else {
            $error = 'Failed to send message. Please try again.';
        }
    }
}
?>

<section class="section contact-page">
    <div class="container">
        <h1 class="section-title"><?= __('contact.title') ?></h1>
        
        <div class="contact-info">
            <div class="contact-info-item">
                <h3>Pakamera</h3>
                <p><a href="https://www.pakamera.pl/the-feltee-handcraft-studio-0_s12775599.htm" target="_blank">pakamera.pl/feltee</a></p>
            </div>
            <div class="contact-info-item">
                <h3>Email</h3>
                <p>contact@feltee.com</p>
            </div>
            <div class="contact-info-item">
                <h3>Studio</h3>
                <p>Kyrgyzstan / Poland</p>
            </div>
        </div>
        
        <div class="contact-form">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= __('contact.success') ?>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-error">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="name"><?= __('contact.form.name') ?> *</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email"><?= __('contact.form.email') ?> *</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="message"><?= __('contact.form.message') ?> *</label>
                    <textarea id="message" name="message" rows="5" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%;"><?= __('contact.form.submit') ?></button>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>