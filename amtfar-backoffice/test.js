fetch('http://amtfar-api.test/api/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ username: 'admin', password: '123', type: 'backoffice' })
}).then(r => r.json()).then(data => {
  console.log("Token Generado OK. Consultando el dashboard...");
  return fetch('http://amtfar-api.test/api/v1/admin/dashboard', {
    headers: { 'Authorization': 'Bearer ' + data.data.token }
  });
}).then(r => r.text()).then(console.log).catch(console.error);
