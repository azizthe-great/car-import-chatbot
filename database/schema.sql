CREATE DATABASE IF NOT EXISTS car_import_chatbot;
USE car_import_chatbot;

CREATE TABLE IF NOT EXISTS faq (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(255) NOT NULL,
    response TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS chat_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) DEFAULT 'Guest',
    message TEXT NOT NULL,
    reply TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    car_model VARCHAR(255) NOT NULL,
    country VARCHAR(255) NOT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO faq (keyword, response) VALUES
('shipping', 'Shipping usually takes 3 to 8 weeks depending on the port and customs clearance time.'),
('price', 'The total cost depends on the vehicle, route, taxes, and registration charges. We can provide a detailed quote after checking the car details.'),
('documents', 'The documents usually required are the title, invoice, export paperwork, passport copy, and customs forms.'),
('delivery', 'Delivery depends on the shipping schedule, customs clearance, and destination location.'),
('import process', 'The process includes car selection, contract, payment, shipping, customs clearance, and final delivery.'),
('customs', 'Customs charges vary by destination country and vehicle value. We can advise based on the car model, port, and destination rules.');
