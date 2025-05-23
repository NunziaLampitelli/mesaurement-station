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
                    $('#dataTable tbody').empty();
                    return;
                }

                const tbody = $('#dataTable tbody');
                tbody.empty();

                // Dati di riferimento (maj) - senza DeviceId, solo valori
                if (typeof response.refTemperature === 'number' && typeof response.refHumidity === 'number') {
                    $('#avgTemperature').text(response.refTemperature.toFixed(2));
                    $('#avgHumidity').text(response.refHumidity.toFixed(2));

                    tbody.append(`
                        <tr style="background-color:#f0f0f0; font-weight:bold;">
                            <td>Reference</td>
                            <td>${response.refTemperature.toFixed(1)}°C</td>
                            <td>${response.refHumidity.toFixed(1)}%</td>
                            <td>--</td>
                        </tr>
                    `);
                } else {
                    $('#avgTemperature').text('--');
                    $('#avgHumidity').text('--');
                }

                // Ciclo sui dati dei dispositivi (measurements)
                response.data.forEach(item => {
                    // Controlli di sicurezza sui valori
                    const temperature = (typeof item.temperature === 'number') ? item.temperature.toFixed(1) + '°C' : '--';
                    const humidity = (typeof item.humidity === 'number') ? item.humidity.toFixed(1) + '%' : '--';

                    const tempReliable = Array.isArray(response.reliableTemperatureDevices) && response.reliableTemperatureDevices.includes(item.DeviceId);
                    const humReliable = Array.isArray(response.reliableHumidityDevices) && response.reliableHumidityDevices.includes(item.DeviceId);

                    const row = `
                        <tr>
                            <td>${item.DeviceId}</td>
                            <td>${temperature}</td>
                            <td>${humidity}</td>
                            <td>
                                <span style="color: ${tempReliable ? 'green' : 'red'}">Temp: ${tempReliable ? '✓' : '✗'}</span><br>
                                <span style="color: ${humReliable ? 'green' : 'red'}">Hum: ${humReliable ? '✓' : '✗'}</span>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });

                // Lista dispositivi affidabili
                const reliableTempList = Array.isArray(response.reliableTemperatureDevices) ? response.reliableTemperatureDevices.join(', ') : '--';
                const reliableHumList = Array.isArray(response.reliableHumidityDevices) ? response.reliableHumidityDevices.join(', ') : '--';

                $('#reliableDevices').html(`<br>
                    <strong>Temp OK:</strong> ${reliableTempList}<br>
                    <strong>Hum OK:</strong> ${reliableHumList}
                `);
            },
            error: function (xhr, status, error) {
                console.error("❌ Error AJAX:", error);
                alert('Error fetching data: ' + error);
            }
        });
    });
});
