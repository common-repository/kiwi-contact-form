<div class="kiwi-cf-main-faq">
    <h1>FAQ</h1>
    <hr />
    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>1. Where is the settings page for Kiwi Contact Form ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">Log into WordPress and open Contact > Contact Forms.</p>
    </div>

    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>2. How can I add a field to my contact form ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            Insert a form-tag into the Form tab panel field. Kiwi Contact Form allows you to edit the templates of your contact forms and your mail (mail headers and message body) with various “tags.” In the terminology for Kiwi Contact Form, tag means a tiny formed string of type enclosed in square brackets ([ ]).
            <img src="<?php echo plugins_url('kiwi-contact-form/admin/views/images/helper.png') ?>" alt="helper">
        </p>

    </div>

    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>3. Can I see the messages submitted through the contact form ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            Kiwi Contact Form doesn’t save the submitted messages.
        </p>
    </div>

    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>4. Can I implement autoresponder ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            Yes, of course. Simply check the “Use mail (2)” box in the Mail tab panel and set up Mail (2) as the template for autoresponder mail. Mail (2) is an additional mail template which works in the same way as the primary Mail template, but Mail (2) is sent only when Mail has been sent successfully.
        </p>
    </div>

    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>5. Can I place a contact form outside a post ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            Yes. You may place a contact form in a text widget as well.
        </p>
    </div>

    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>6. I get an error message with a red border. So, how can I solve this ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            The red border means that Kiwi Contact Form tried to send mail with wp_mail(), but it failed. To solve the issue, you need to find out the actual reason why it is failing in the first place.
            There could be various reasons such as the mail setup wasn’t valid.
            The sending would also fail if the mail server was down, inaccessible or experiencing other problems.
        </p>
    </div>

    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>7. I want to use contact form in my language, not in English. How can I do that ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            Kiwi Contact Form has not YET been translated into other languages.
        </p>
    </div>

    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>8. CAPTCHA does not work; the image does not show up. What am I supposed to do ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            To use CAPTCHA, you need GD and FreeType library installed on your server. Also, make sure that CAPTCHA’s temporary folder is writable.
        </p>
    </div>


    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>9. How can I export/import contact form data ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            You can export and import form data via Tools > Export (<a href="https://codex.wordpress.org/Tools_Export_Screen">https://codex.wordpress.org/Tools_Export_Screen</a>) and Tools > Import (<a href="https://codex.wordpress.org/Tools_Import_Screen">https://codex.wordpress.org/Tools_Import_Screen</a>)in the WordPress admin screen.
        </p>
    </div>

    <div class="kiwi-cf-faq">
        <div class="kiwi-cf-faq-question">
            <h2>10. I get spam messages through my contact forms. How can I stop them ?</h2>
            <div class="kiwi-cf-faq-toggle"></div>
        </div>
        <p class="kiwi-cf-faq-answer">
            You can protect your contact forms with the anti-spam features that Kiwi Contact Form provides.
            Kiwi Contact Form supports spam-filtering with Akismet.
            Another one is reCAPTCHA which protects you against spam and other types of automated abuse.
        </p>
    </div>
</div>
