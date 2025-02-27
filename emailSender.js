// emailSender.js

const express = require('express');
const nodemailer = require('nodemailer');
const bodyParser = require('body-parser');

// Create an Express app
const app = express();
const port = 3001;

// Middleware to parse JSON body data
app.use(bodyParser.json());

// Create a transport for nodemailer to send email
const transporter = nodemailer.createTransport({
  service: 'gmail', // You can use other services like Outlook, Yahoo, etc.
  auth: {
    user: 'cjaycamposagrado102@gmail.com', // Replace with your email
    pass: 'Belatebayo1'   // Replace with your email password or app-specific password
  }
});

// Route to handle email sending
app.post('/send-email', (req, res) => {
  const { subject, message } = req.body;

  const mailOptions = {
    from: 'cjaycamposagrado102@gmail.com',   // Replace with your email
    to: 'ccjaycamposagrado@gmail.com', // Replace with recipient email
    subject: subject,
    text: message
  };

  // Send email using nodemailer
  transporter.sendMail(mailOptions, (error, info) => {
    if (error) {
      return res.status(500).send('Error sending email: ' + error);
    }
    res.status(200).send('Email sent successfully: ' + info.response);
  });
});

// Start the server
app.listen(port, () => {
  console.log(`Email sender server running at http://localhost:${port}`);
});
