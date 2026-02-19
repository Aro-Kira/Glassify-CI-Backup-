<!DOCTYPE html>
<html>
<head>
    <title>Test Appointment Update</title>
</head>
<body>
    <h1>Test Appointment Update</h1>
    <button onclick="testUpdate()">Test Update</button>
    <pre id="result"></pre>

    <script>
    async function testUpdate() {
        const formData = new FormData();
        formData.append('appointment_id', '1'); // Use real appointment ID
        formData.append('notes', 'Test update');
        
        try {
            const response = await fetch('<?php echo "http://localhost/Glassify-CI/AdminCon/update_appointment_ajax"; ?>', {
                method: 'POST',
                body: formData
            });
            
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            const text = await response.text();
            console.log('Response text:', text);
            document.getElementById('result').textContent = text;
            
            try {
                const json = JSON.parse(text);
                console.log('Parsed JSON:', json);
            } catch(e) {
                console.error('Failed to parse JSON:', e);
            }
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('result').textContent = 'Error: ' + error.message;
        }
    }
    </script>
</body>
</html>
