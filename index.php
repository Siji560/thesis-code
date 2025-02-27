<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script type="text/javascript"
        src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js">
</script>
<script type="text/javascript">
   (function(){
      emailjs.init({
        publicKey: "X32k0BbWGVp3dEmhF",
      });
   })();
</script>
<script src = "script.js"></script>

<script>
    $(document).ready(function() {
        // Load sensor data
        function loadSensorData() {
            $("#temperature-value").load("fetch_temperature.php");
            $("#turbidity-value").load("fetch_turbidity.php");
            $("#ph-value").load("fetch_ph.php");
        }

        function loadSensorLimit() {
            $('#turbidity-limit').load("fetch_turblimit.php");
            $('#ph-limit').load("fetch_phlimit.php");
            $("#temperature-limit").load("fetch_templimit.php");
        }

       function loadTimeAndLighting() {
    const currentTime = new Date();
    const currentHours = currentTime.getHours();
    const currentMinutes = currentTime.getMinutes();

    const lightingStartTime = localStorage.getItem("lightingStartTime");
    const lightingEndTime = localStorage.getItem("lightingEndTime");

    if (lightingStartTime && lightingEndTime) {
        const [startHours, startMinutes] = lightingStartTime.split(":").map(Number);
        const [endHours, endMinutes] = lightingEndTime.split(":").map(Number);

        let isOn;

        if (startHours < endHours || (startHours === endHours && startMinutes < endMinutes)) {
            // Normal range (e.g., 9 AM to 5 PM)
            isOn = (currentHours > startHours || 
                    (currentHours === startHours && currentMinutes >= startMinutes)) &&
                   (currentHours < endHours || 
                    (currentHours === endHours && currentMinutes < endMinutes));
        } else {
            // Range crosses midnight (e.g., 9 PM to 2 AM)
            isOn = (currentHours > startHours || 
                    (currentHours === startHours && currentMinutes >= startMinutes)) ||
                   (currentHours < endHours || 
                    (currentHours === endHours && currentMinutes < endMinutes));
        }

        const lightingStatus = isOn ? "ON" : "OFF";

        // Update lighting status on the webpage
        const statusElement = $("#lighting-status");
        if (statusElement.length) {
            statusElement.text(`Lighting: ${lightingStatus}`);
        } else {
            console.error("#lighting-status element not found.");
        }

        // Send the lighting status to the database
        $.post("update_lighting_status.php", {
            status: lightingStatus
        }).done(function(response) {
            console.log("Server Response:", response);
        }).fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Error:", textStatus, errorThrown);
        });
    } else {
        console.warn("Lighting start or end time is not set in localStorage.");
    }
}

// Call the function after DOM is ready
$(document).ready(function() {
    loadTimeAndLighting();
});




        let phBlinkInterval = null;
        let turbidityBlinkInterval = null;
        let temperatureBlinkInterval = null;

        function changecolor1() {
            const phValueText = document.getElementById("ph-value").innerText;
            const phLimitText = document.getElementById("ph-limit").innerText;
            const phValue = parseFloat(phValueText.match(/[\d.]+/));  
            const phLimit = parseFloat(phLimitText.match(/[\d.]+/));  
            const phElement = document.getElementById("ph");

            if (!isNaN(phValue) && !isNaN(phLimit)) {
                if (phValue >= phLimit) {
                    if (!phBlinkInterval) {
                        phBlinkInterval = setInterval(() => {
                            phElement.style.backgroundColor = 
                                phElement.style.backgroundColor === "red" ? "" : "red";
                        }, 500);
                        sendMail();
                    }
                } else {
                    clearInterval(phBlinkInterval);
                    phBlinkInterval = null;
                    phElement.style.backgroundColor = "";
                }
            }
        }

        function changecolor2() {
            const TurbidityValueText = document.getElementById("turbidity-value").innerText;
            const TurbidityLimitText = document.getElementById("turbidity-limit").innerText;
            const TurbidityValue = parseFloat(TurbidityValueText.match(/[\d.]+/));  
            const TurbidityLimit = parseFloat(TurbidityLimitText.match(/[\d.]+/));  
            const TurbidityElement = document.getElementById("turbidity");

            if (!isNaN(TurbidityValue) && !isNaN(TurbidityLimit)) {
                if (TurbidityValue <= TurbidityLimit) {
                    if (!turbidityBlinkInterval) {
                        turbidityBlinkInterval = setInterval(() => {
                            TurbidityElement.style.backgroundColor = 
                                TurbidityElement.style.backgroundColor === "red" ? "" : "red";
                        }, 500);
                        sendMail();
                    }
                } else {
                    clearInterval(turbidityBlinkInterval);
                    turbidityBlinkInterval = null;
                    TurbidityElement.style.backgroundColor = "";
                }
            }
        }

        function changecolor3() {
            const TemperatureValueText = document.getElementById("temperature-value").innerText;
            const TemperatureLimitText = document.getElementById("temperature-limit").innerText;
            const TemperatureValue = parseFloat(TemperatureValueText.match(/[\d.]+/));  
            const TemperatureLimit = parseFloat(TemperatureLimitText.match(/[\d.]+/));  
            const TemperatureElement = document.getElementById("temperature");

            if (!isNaN(TemperatureValue) && !isNaN(TemperatureLimit)) {
                if (TemperatureValue >= TemperatureLimit) {
                    if (!temperatureBlinkInterval) {
                        temperatureBlinkInterval = setInterval(() => {
                            TemperatureElement.style.backgroundColor = 
                                TemperatureElement.style.backgroundColor === "red" ? "" : "red";
                        }, 500);
                        sendMail();
                    }
                } else {
                    clearInterval(temperatureBlinkInterval);
                    temperatureBlinkInterval = null;
                    TemperatureElement.style.backgroundColor = "";
                }
            }
        }

        setInterval(() => {
            loadSensorData();
            loadTimeAndLighting();
            loadSensorLimit();
            changecolor1();
            changecolor2();
            changecolor3();
        }, 1000);

        loadSensorData();
        loadTimeAndLighting();
        loadSensorLimit();

        $("#set-ph-limit").click(function() {
            postLimit('pHThreshold');
        });

        $("#set-turbidity-limit").click(function() {
            postLimit('TurbidityThreshold');
        });

        $("#set-temperature-limit").click(function() {
            postLimit('TempThreshold');
        });

        $("#set-feeder-time").click(function() {
            setFeederTime();
        });

        $("#set-lighting-time").click(function() {
            setLightingTime();
        });
    });

    function postLimit(sensorType) {
        let newLimit = $("#" + sensorType + "-input").val();

        if (newLimit && !isNaN(newLimit) && newLimit > 0) {
            $.post("set_limit.php", { type: sensorType, value: newLimit }, function(response) {
                alert(response);
                $("#" + sensorType + "-limit").text(`Current Limit: ${newLimit}`);
                loadSensorData();
            }).fail(function() {
                alert("Error updating limit");
            });
        } else {
            alert("Please enter a valid value (positive number).");
        }
    }

    // Modify the setFeederTime function to store the custom interval in localStorage
// Function to set feeder time and feed amount
let intervalId;

        // Function to set feeder time
        function setFeederTime() {
            const hours = parseInt($("#feeding-hours").val()) || 0;
            const minutes = parseInt($("#feeding-minutes").val()) || 0;
            const grams = parseFloat($("#feeding-grams").val()) || 0;

            if (grams <= 0) {
                alert("Feed amount must be greater than 0!");
                return;
            }

            // Format and store the interval in localStorage
            const feedingInterval = `${hours} hour(s) and ${minutes} minute(s)`;
            localStorage.setItem("feedingInterval", feedingInterval);
            localStorage.setItem("feedingAmount", grams);

            // Update displayed values
            $("#feeder-interval").text(`Current interval: ${feedingInterval}`);
            $("#feeder-amount").text(`Current feed time: ${grams} second(s)`);

            // Start feeding process
            startFeedingProcess(hours, minutes, grams);
        }

        // Function to start feeding process
        function startFeedingProcess(hours, minutes, grams) {
            const intervalMs = (hours * 60 + minutes) * 60 * 1000;

            if (intervalMs <= 0) {
                alert("Invalid interval! Set hours or minutes greater than zero.");
                return;
            }

            // Clear existing interval
            if (intervalId) clearInterval(intervalId);

            // Start a new interval
            intervalId = setInterval(() => {
                sendFeedingData(grams);
            }, intervalMs);
        }

        // Function to send feeding data to the server
        function sendFeedingData(grams) {
            const data = {
                status: "ON",
                grams: grams,
            };

            fetch('feed_endpoint.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                console.log("Feeding data sent:", data);
            })
            .catch(error => {
                console.error("Error sending feeding data:", error);
            });
        }

        // On page load
        $(document).ready(function() {
            // Load stored interval and amount
            const storedInterval = localStorage.getItem("feedingInterval");
            const storedAmount = localStorage.getItem("feedingAmount");

            if (storedInterval) {
                $("#feeder-interval").text(`Current interval: ${storedInterval}`);
            }

            if (storedAmount) {
                $("#feeder-amount").text(`Current feed time: ${storedAmount} second(s)`);
            }

            // Set feeder time button click
            $("#set-feeder-time").click(function() {
                setFeederTime();
            });
        });


function setLightingTime() {
    const lightingStartInput = $("#lighting-start-input").val();
    const lightingEndInput = $("#lighting-end-input").val();

    if (lightingStartInput && lightingEndInput) {
        // Store in localStorage
        localStorage.setItem("lightingStartTime", lightingStartInput);
        localStorage.setItem("lightingEndTime", lightingEndInput);

        // Update the displayed time
        $("#lighting-time").text(`Current time range: ${lightingStartInput} - ${lightingEndInput}`);
        alert("Lighting time updated!");
    } else {
        alert("Please enter valid lighting start and end times.");
    }
}

function loadStoredTimerSettings() {
    const feederStartTime = localStorage.getItem("feederStartTime");
    const feederEndTime = localStorage.getItem("feederEndTime");
    const lightingStartTime = localStorage.getItem("lightingStartTime");
    const lightingEndTime = localStorage.getItem("lightingEndTime");

    if (feederStartTime && feederEndTime) {
        $("#feeder-time").text(`Current time range: ${feederStartTime} - ${feederEndTime}`);
    } else {
        $("#feeder-time").text("No feeder time set.");
    }

    if (lightingStartTime && lightingEndTime) {
        $("#lighting-time").text(`Current time range: ${lightingStartTime} - ${lightingEndTime}`);
    } else {
        $("#lighting-time").text("No lighting time set.");
    }
}

$(document).ready(function() {
    loadStoredTimerSettings();

    $("#set-feeder-time").click(function() {
        setFeederTime();
    });

    $("#set-lighting-time").click(function() {
        setLightingTime();
    });
});

</script>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aquarium Monitoring System</title>
    <style>
        /* General Styles */
/* General Styles */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f1f1f1;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Container */
.container {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 10px;
    width: 1080px;
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Header */
h1 {
    text-align: center;
    font-size: 40px;
    color: #1d5a5a;
    margin-bottom: 30px;
}

/* Feeder Interval Section */
.feeder-settings {
    background-color: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    margin-bottom: 30px;
    width: 100%;
}

.feeder-settings h2 {
    font-size: 24px;
    color: #1d5a5a;
    margin-bottom: 20px;
}

.input-group {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    width: 100%;
}

.input-group label {
    font-size: 16px;
    color: #555;
    width: 30%;
}

.input-group input {
    width: 60%;
    padding: 12px;
    border: 2px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    text-align: center;
    background-color: #e8f9f3;
}

.input-group input:focus {
    border-color: #1d5a5a;
}

.set-time-button {
    background-color: #1d5a5a;
    color: white;
    padding: 12px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    width: 100%;
    margin-top: 10px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.set-time-button:hover {
    background-color: #146c64;
}

.interval-display {
    margin-top: 20px;
    font-size: 16px;
    color: #333;
    font-weight: bold;
}

/* Box Styling (For other sections) */
.row {
    display: flex;
    justify-content: space-around;
    width: 100%;
    margin-bottom: 30px;
}

.box {
    background-color: #fafafa;
    padding: 20px;
    border-radius: 10px;
    width: 30%;
    text-align: center;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease;
}

.box:hover {
    background-color: #e2f7f0;
}

.box input[type="time"],
.box input[type="button"],
.sensor-box input[type="number"] {
    width: 85%;
    padding: 12px;
    margin-top: 15px;
    border: 2px solid #ccc;
    border-radius: 5px;
    background-color: #e8f9f3;
    font-size: 16px;
    transition: border-color 0.3s;
}

.box input[type="time"]:focus,
.box input[type="button"]:focus,
.sensor-box input[type="number"]:focus {
    border-color: #1d5a5a;
}

/* Sensor Section */
.sensor-group {
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.sensor-box {
    background-color: #f9f9f9;
    padding: 15px;
    border-radius: 10px;
    width: 30%;
    text-align: center;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.sensor-box h2 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #1d5a5a;
}

.sensor-box span {
    display: block;
    font-size: 16px;
    margin-top: 5px;
    color: #333;
}

.sensor-box input[type="number"] {
    margin-top: 10px;
}

/* Button Group */
.button-group {
    text-align: center;
    margin-top: 20px;
}

.button-group input[type="button"] {
    width: auto;
    padding: 10px 20px;
    margin: 5px;
    border: none;
    border-radius: 5px;
    background-color: #1d5a5a;
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.button-group input[type="button"]:hover {
    background-color: #146c64;
}


    </style>
</head>
<body>
    <div class="container">
        <h1>Aquarium Monitoring System</h1>
        <div class="row">
            <div id="temperature" class="sensor-box">
                <h2>Temperature</h2>
                <span id="temperature-value">Loading...</span>
                <span id="temperature-limit">Current Limit: Loading...</span>
                <input type="number" id="TempThreshold-input" placeholder="Set new limit" />
                <input type="button" id="set-temperature-limit" value="Set Limit" />
            </div>
            <div id="turbidity" class="sensor-box">
                <h2>Turbidity</h2>
                <span id="turbidity-value">Loading...</span>
                <span id="turbidity-limit">Current Limit: Loading...</span>
                <input type="number" id="TurbidityThreshold-input" placeholder="Set new limit" />
                <input type="button" id="set-turbidity-limit" value="Set Limit" />
            </div>
            <div id="ph" class="sensor-box">
                <h2>pH Level</h2>
                <span id="ph-value">Loading...</span>
                <span id="ph-limit">Current Limit: Loading...</span>
                <input type="number" id="pHThreshold-input" placeholder="Set new limit" />
                <input type="button" id="set-ph-limit" value="Set Limit" />
            </div>
        </div>
        <div class="row">
            <div class="box">
                <h2>Lighting Time</h2>
                <input type="time" id="lighting-start-input" placeholder="Start Time" />
                <input type="time" id="lighting-end-input" placeholder="End Time" />
                <input type="button" id="set-lighting-time" value="Set Lighting Time" />
                <span id="lighting-time">Current time range: Not Set</span>
            </div>
            <div class="feeder-settings">
    <h2>Feeder</h2>
    <div class="input-group">
        <label for="feeding-hours">Hours:</label>
        <input type="number" id="feeding-hours" min="0" value="0" class="time-input" />
    </div>
    <div class="input-group">
        <label for="feeding-minutes">Minutes:</label>
        <input type="number" id="feeding-minutes" min="0" value="0" class="time-input" />
    </div>
    <div class="input-group">
        <label for="feeding-grams">Feed Time (seconds):</label>
        <input type="number" id="feeding-grams" min="0" value="0" class="time-input" />
    </div>

    <button id="set-feeder-time" class="set-time-button">Set Feeder Interval</button>
    <div id="feeder-interval" class="interval-display">Current interval: Not Set</div>
    <div id="feeder-amount" class="interval-display">Current feeding time: Not Set</div>
</div>


        </div>
        <div id="current-time" class="box">
            <h2>Current Time</h2>
            <span>Loading...</span>
        </div>
    </div>
</body>
</html>


