// Premium Calculation with AJAX
document.getElementById('calculate').addEventListener('click', function() {
    const carValue = document.getElementById("car-value").value;
    const risk = document.getElementById("risk").value;
    const carAge = document.getElementById("car-age").value;
    const vehicleId = document.getElementById("vehicle-id").value;

    // Perform the calculation
    const data = new FormData();
    data.append('action', 'calculate');
    data.append('car_value', carValue);
    data.append('risk_exposure', risk);
    data.append('car_age', carAge);
    data.append('vehicle_id', vehicleId);

    fetch('../php/premium.php', {
        method: 'POST',
        body: data
    })
    .then(response => response.text())
    .then(result => {
        document.getElementById('premium-result').innerText = result;
    })
    .catch(error => console.log('Error:', error));
});
