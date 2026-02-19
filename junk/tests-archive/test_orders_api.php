<!DOCTYPE html>
<html>
<head>
    <title>Test Orders API</title>
</head>
<body>
    <h1>Test Orders API</h1>
    <button onclick="testAPI()">Test Get Orders</button>
    <pre id="result"></pre>
    
    <script>
        async function testAPI() {
            const resultEl = document.getElementById('result');
            resultEl.textContent = 'Loading...';
            
            try {
                const url = 'http://localhost/Glassify-CI/AdminCon/get_orders_ajax?status=all&page=1&limit=10';
                console.log('Fetching:', url);
                
                const response = await fetch(url);
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const text = await response.text();
                console.log('Response text:', text);
                
                resultEl.textContent = `Status: ${response.status}\n\n${text}`;
                
                // Try to parse as JSON
                try {
                    const json = JSON.parse(text);
                    resultEl.textContent += '\n\nParsed JSON:\n' + JSON.stringify(json, null, 2);
                } catch(e) {
                    resultEl.textContent += '\n\nNot valid JSON';
                }
            } catch (error) {
                resultEl.textContent = 'Error: ' + error.message;
                console.error('Error:', error);
            }
        }
    </script>
</body>
</html>
