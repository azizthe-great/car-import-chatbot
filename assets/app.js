const chatForm = document.getElementById('chatForm');
const userInput = document.getElementById('userInput');
const chatWindow = document.getElementById('chatWindow');
const leadForm = document.getElementById('leadForm');

function appendMessage(sender, text) {
  const messageRow = document.createElement('div');
  messageRow.className = `message ${sender}`;

  const bubble = document.createElement('p');
  bubble.textContent = text;

  messageRow.appendChild(bubble);
  chatWindow.appendChild(messageRow);
  chatWindow.scrollTop = chatWindow.scrollHeight;
}

async function sendMessage(message) {
  appendMessage('user', message);
  userInput.value = '';

  try {
    const response = await fetch('api/chat.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        message: message
      })
    });

    const data = await response.json();
    appendMessage('bot', data.reply || 'I am sorry, I could not process your request.');
  } catch (error) {
    appendMessage('bot', 'The chatbot is temporarily unavailable. Please try again later.');
    console.error(error);
  }
}

async function submitLead(formData) {
  try {
    const response = await fetch('api/chat.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        lead: formData
      })
    });

    const data = await response.json();
    const successMessage = data.reply || 'Thank you. Your inquiry has been received.';
    appendMessage('bot', successMessage);
    return data;
  } catch (error) {
    appendMessage('bot', 'Your inquiry could not be sent right now. Please try again later.');
    console.error(error);
    return null;
  }
}

chatForm.addEventListener('submit', function (event) {
  event.preventDefault();

  const text = userInput.value.trim();
  if (!text) return;

  sendMessage(text);
});

leadForm.addEventListener('submit', async function (event) {
  event.preventDefault();

  const formData = Object.fromEntries(new FormData(leadForm).entries());
  const hasRequired = ['name', 'phone', 'email', 'car_model', 'country'].every((field) =>
    String(formData[field] || '').trim() !== ''
  );

  if (!hasRequired) {
    appendMessage('bot', 'Please complete your name, phone, email, car model, and destination country.');
    return;
  }

  const payload = {
    name: formData.name,
    phone: formData.phone,
    email: formData.email,
    car_model: formData.car_model,
    country: formData.country,
    message: formData.message || 'No extra message provided.'
  };

  await submitLead(payload);
  leadForm.reset();
});
