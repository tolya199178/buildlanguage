var theForm = document.getElementById('theForm');
new stepsForm(theForm, {
    onSubmit: function (form) {
        classie.addClass(theForm.querySelector('.simform-inner'), 'hide');
        $.ajax({
            type: "POST",
            url: "contact/sent",
            data: $(theForm).serializeArray() // changed
        });
        var messageEl = theForm.querySelector('.final-message');
        messageEl.innerHTML = 'Thank you! We\'ll be in touch.';
        classie.addClass(messageEl, 'show');
    }
});
