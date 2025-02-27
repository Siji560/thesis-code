function sendMail() {
    let parms = {
        name: "cjay",
        email: "ccjaycamposagrado@gmail.com",
        subject: "subject",
        message: "hi",
    };

    emailjs.send("service_skqwm9k", "template_dfqdbyd", parms)
        .then(function(response) {
            alert("Email Sent Successfully!");
        }, function(error) {
            alert("Failed to send email. Error: " + JSON.stringify(error));
        });
}
