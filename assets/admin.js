async function loadAdminData() {
  try {
    const response = await fetch('api/admin.php');
    const data = await response.json();

    renderFaq(data.faq || []);
    renderInquiries(data.inquiries || []);
    renderLogs(data.logs || []);
  } catch (error) {
    console.error(error);
  }
}

function renderFaq(items) {
  const list = document.getElementById('faqList');
  list.innerHTML = '';

  if (!items.length) {
    list.innerHTML = '<div class="list-item">No FAQ items yet.</div>';
    return;
  }

  items.forEach((item) => {
    const el = document.createElement('div');
    el.className = 'list-item';
    el.innerHTML = `
      <strong>Keyword:</strong> ${item.keyword}<br>
      <strong>Reply:</strong> ${item.response}
    `;
    list.appendChild(el);
  });
}

function renderInquiries(items) {
  const list = document.getElementById('inquiriesList');
  list.innerHTML = '';

  if (!items.length) {
    list.innerHTML = '<div class="list-item">No inquiries yet.</div>';
    return;
  }

  items.forEach((item) => {
    const el = document.createElement('div');
    el.className = 'list-item';
    el.innerHTML = `
      <strong>${item.name}</strong><br>
      Phone: ${item.phone}<br>
      Email: ${item.email}<br>
      Car: ${item.car_model}<br>
      Country: ${item.country}<br>
      Message: ${item.message || 'No message'}
    `;
    list.appendChild(el);
  });
}

function renderLogs(items) {
  const list = document.getElementById('logsList');
  list.innerHTML = '';

  if (!items.length) {
    list.innerHTML = '<div class="list-item">No chat activity yet.</div>';
    return;
  }

  items.forEach((item) => {
    const el = document.createElement('div');
    el.className = 'list-item';
    el.innerHTML = `
      <strong>${item.customer_name}</strong><br>
      User: ${item.message}<br>
      Reply: ${item.reply}
    `;
    list.appendChild(el);
  });
}

document.getElementById('faqForm').addEventListener('submit', async function (event) {
  event.preventDefault();

  const keyword = document.getElementById('faqKeyword').value.trim();
  const response = document.getElementById('faqResponse').value.trim();
  const status = document.getElementById('faqStatus');

  try {
    const result = await fetch('api/admin.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ keyword, response })
    });

    const data = await result.json();
    status.textContent = data.message || 'Saved successfully.';
    document.getElementById('faqForm').reset();
    loadAdminData();
  } catch (error) {
    status.textContent = 'Failed to save FAQ.';
    console.error(error);
  }
});

loadAdminData();
