jQuery(document).ready(function ($) {
    $(".kiwi-cf-faq-question").click(function () {
        var container = $(this).parents(".kiwi-cf-faq");
        var answer = container.find(".kiwi-cf-faq-answer");
        var trigger = container.find(".kiwi-cf-faq-toggle");

        answer.slideToggle(200);

        if (trigger.hasClass("kiwi-cf-faq-toggle-open")) {
            trigger.removeClass("kiwi-cf-faq-toggle-open");
        } else {
            trigger.addClass("kiwi-cf-faq-toggle-open");
        }

        if (container.hasClass("kiwi-cf-faq-expanded")) {
            container.removeClass("kiwi-cf-faq-expanded");
        } else {
            container.addClass("kiwi-cf-faq-expanded");
        }
    });
});