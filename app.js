$(document).ready(function () {
    $('#loadButton').click(function () {
        const date = $('#datePicker').val();
        const time = $('#timePicker').val();

        if (!date || !time) {
            alert('Please select a date and time.');
            return;
        }

        $.ajax({
            url: 'getData.php',
            method: 'GET',
            dataType: 'json',
            data: {
                date: date,
                time: time,
                cacheBuster: new Date().getTime()
            },
            success: function (response) {
                console.log("✅ Data received:", response);

                if (!response || !response.data || !Array.isArray(response.data) || response.data.length === 0) {
                    alert("No data available for this time and date.");
                    $('#avgTemperature').text('--');
                    $('#avgHumidity').text('--');
                    $('#reliableDevices').html('--');
                    return;
                }

                const tbody = $('#dataTable tbody');
                tbody.empty();

                // Controllo dati riferimento e aggiornamento summary
                if (response.refTemperature !== undefined && response.refHumidity !== undefined) {
                    $('#avgTemperature').text(response.refTemperature.toFixed(2));
                    $('#avgHumidity').text(response.refHumidity.toFixed(2));

                    tbody.append(`
                        <tr style="background-color:#f0f0f0; font-weight:bold;">
                            <td>Riferimento</td>
                            <td>${response.refTemperature.toFixed(1)}°C</td>
                            <td>${response.refHumidity.toFixed(1)}%</td>
                            <td>--</td>
                        </tr>
                    `);
                } else {
                    $('#avgTemperature').text('--');
                    $('#avgHumidity').text('--');
                }

                // Ciclo sensori
                response.data.forEach(item => {
                    const tempReliable = response.reliableTemperatureDevices.includes(item.DeviceId);
                    const humReliable = response.reliableHumidityDevices.includes(item.DeviceId);

                    const row = `
                        <tr>
                            <td>${item.DeviceId}</td>
                            <td>${item.temperature.toFixed(1)}°C</td>
                            <td>${item.humidity.toFixed(1)}%</td>
                            <td>
                                <span style="color: ${tempReliable ? 'green' : 'red'}">Temp: ${tempReliable ? '✓' : '✗'}</span><br>
                                <span style="color: ${humReliable ? 'green' : 'red'}">Hum: ${humReliable ? '✓' : '✗'}</span>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Mostro dispositivi affidabili
                $('#reliableDevices').html(`
                    <strong>Temp OK:</strong> ${response.reliableTemperatureDevices.join(', ')}<br>
                    <strong>Hum OK:</strong> ${response.reliableHumidityDevices.join(', ')}
                `);
            },
            error: function (xhr, status, error) {
                console.error("❌ Error AJAX:", error);
                alert('Error fetching data: ' + error);
            }
        });
    });
});
