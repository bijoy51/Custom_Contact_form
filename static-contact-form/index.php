<?php get_header(); ?>

<div class="container">
    <h2>Contact Us</h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <!-- nonce -->
          <?php wp_nonce_field('ccf_form_nonce_action', 'ccf_form_nonce_field'); ?>        
        <div class="input-box">
            <label for="name">Your Name</label>
            <input type="text" name="ccf_name" id="name" placeholder="Enter your name">
        </div>

        <div class="input-box">
            <label for="email">Your Email</label>
            <input type="email" name="ccf_email" id="email" placeholder="Enter your email">
        </div>

        <div class="input-box">
            <label for="message">Message</label>
            <textarea name="ccf_message" id="message" rows="5" placeholder="Write your message"></textarea>
        </div>
        
        <!-- honeypot anti-spam -->
         <input type="text" name="ccf_honey" style="display:none">
          
          <!-- action identifier -->
           <input type="hidden" name="action" value="ccf_handle_form">

        <button type="submit" class="submit">Send Message</button>
    </form>
</div>

<?php get_footer(); ?>
