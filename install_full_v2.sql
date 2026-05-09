-- Phoenix Arabia Full Stack Hostinger v2
-- Includes: Marketplace, RFQ, Customers, Staff, Supplier Management, Permissions, SEO Engine, Brand Pages, Industry Pages, Article Engine.
-- Import this file into phpMyAdmin after creating database u665392070_phoenix.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+03:00";

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  slug VARCHAR(160) UNIQUE,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS brands (
  id INT AUTO_INCREMENT PRIMARY KEY,
  brand_name VARCHAR(190) NOT NULL UNIQUE,
  slug VARCHAR(220) NOT NULL UNIQUE,
  public_description TEXT,
  seo_title VARCHAR(255),
  meta_description VARCHAR(320),
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS industry_pages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  industry_name VARCHAR(190) NOT NULL UNIQUE,
  slug VARCHAR(220) NOT NULL UNIQUE,
  headline VARCHAR(255),
  intro TEXT,
  seo_title VARCHAR(255),
  meta_description VARCHAR(320),
  keywords TEXT,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(220) NOT NULL,
  slug VARCHAR(260) UNIQUE,
  brand VARCHAR(160),
  category VARCHAR(160),
  sku VARCHAR(120),
  price_mode ENUM('Show Price','Request Price','RFQ Only','MTO') NOT NULL DEFAULT 'RFQ Only',
  fixed_price DECIMAL(14,2) NULL,
  currency VARCHAR(10) DEFAULT 'SAR',
  unit VARCHAR(50),
  short_description VARCHAR(255),
  description TEXT,
  image_path VARCHAR(255) DEFAULT 'assets/placeholder-product.svg',
  datasheet_path VARCHAR(255),
  manufacturer_visible TINYINT(1) NOT NULL DEFAULT 0,
  manufacturer_name VARCHAR(190),
  internal_supplier_name VARCHAR(190),
  country_of_origin VARCHAR(120),
  minimum_order_qty VARCHAR(80),
  lead_time VARCHAR(120),
  stock_status ENUM('In Stock','Limited Stock','Upon Request','MTO') NOT NULL DEFAULT 'Upon Request',
  seo_title VARCHAR(255),
  meta_description VARCHAR(320),
  keywords TEXT,
  canonical_url VARCHAR(255),
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_product_category (category),
  INDEX idx_product_brand (brand),
  INDEX idx_product_price_mode (price_mode),
  INDEX idx_product_active (is_active),
  FULLTEXT KEY ft_product_search (name, brand, category, sku, short_description, description, keywords)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_accounts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(160) NOT NULL,
  company_name VARCHAR(190),
  mobile VARCHAR(80) NOT NULL UNIQUE,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  account_status ENUM('Active','Suspended','Pending') NOT NULL DEFAULT 'Active',
  assigned_account_manager INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_customer_mobile (mobile),
  INDEX idx_customer_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  reset_token VARCHAR(120) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reset_token (reset_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rfq_cart_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NULL,
  session_id VARCHAR(120) NULL,
  product_id INT NULL,
  product_name VARCHAR(220) NOT NULL,
  brand VARCHAR(160),
  category VARCHAR(160),
  quantity VARCHAR(80),
  notes TEXT,
  price_mode ENUM('Show Price','Request Price','RFQ Only','MTO') NOT NULL DEFAULT 'RFQ Only',
  status ENUM('Active','Submitted','Abandoned','Removed') NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_cart_customer (customer_id),
  INDEX idx_cart_session (session_id),
  INDEX idx_cart_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rfq_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NULL,
  company VARCHAR(190) NOT NULL,
  contact_name VARCHAR(160) NOT NULL,
  mobile VARCHAR(80) NOT NULL,
  email VARCHAR(190),
  request_type VARCHAR(120),
  product_name VARCHAR(220),
  details TEXT,
  file_path VARCHAR(255),
  status ENUM('New','Reviewing','Quoted','Won','Lost','Closed') NOT NULL DEFAULT 'New',
  priority ENUM('Low','Normal','High','Urgent') NOT NULL DEFAULT 'Normal',
  estimated_value DECIMAL(14,2) NULL,
  source VARCHAR(80) DEFAULT 'Website',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_rfq_customer (customer_id),
  INDEX idx_rfq_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS customer_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  rfq_id INT NULL,
  order_reference VARCHAR(80) NOT NULL UNIQUE,
  request_type VARCHAR(120),
  total_items INT DEFAULT 0,
  status ENUM('Draft','Submitted','Reviewing','Quoted','Won','Lost','Closed') NOT NULL DEFAULT 'Submitted',
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_orders_customer (customer_id),
  INDEX idx_orders_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS staff_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  mobile VARCHAR(80) UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('Super Admin','Sales Agent','Account Manager','Procurement Officer','Data Entry') NOT NULL DEFAULT 'Sales Agent',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_staff_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS account_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT NOT NULL,
  customer_id INT NOT NULL,
  assigned_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_assignment (staff_id, customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_name VARCHAR(220) NOT NULL,
  supplier_type ENUM('Manufacturer','Distributor','Agent','Service Provider','Logistics Partner','Other') NOT NULL DEFAULT 'Manufacturer',
  country VARCHAR(120),
  city VARCHAR(120),
  website VARCHAR(220),
  factory_location TEXT,
  product_categories TEXT,
  commercial_status ENUM('Prospect','Approved','Preferred','Blocked','Inactive') NOT NULL DEFAULT 'Prospect',
  visibility_to_public TINYINT(1) NOT NULL DEFAULT 0,
  notes TEXT,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS supplier_contacts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT NOT NULL,
  contact_name VARCHAR(160) NOT NULL,
  job_title VARCHAR(160),
  mobile VARCHAR(80),
  whatsapp VARCHAR(80),
  email VARCHAR(190),
  department VARCHAR(120),
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS supplier_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT NOT NULL,
  document_type ENUM('Commercial Registration','VAT Certificate','ISO Certificate','Product Certificate','Agency Agreement','Price List','Catalogue','License','Other') NOT NULL DEFAULT 'Other',
  document_title VARCHAR(220),
  file_path VARCHAR(255),
  expiry_date DATE NULL,
  notes TEXT,
  uploaded_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS supplier_products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_id INT NOT NULL,
  public_product_id INT NULL,
  product_name VARCHAR(220) NOT NULL,
  brand VARCHAR(160),
  category VARCHAR(160),
  sku VARCHAR(120),
  part_number VARCHAR(160),
  short_description VARCHAR(255),
  technical_description TEXT,
  unit VARCHAR(50),
  moq VARCHAR(80),
  lead_time VARCHAR(120),
  origin_country VARCHAR(120),
  price_mode ENUM('Show Price','Request Price','RFQ Only','MTO') NOT NULL DEFAULT 'RFQ Only',
  cost_price DECIMAL(14,2) NULL,
  selling_price DECIMAL(14,2) NULL,
  currency VARCHAR(10) DEFAULT 'SAR',
  show_on_website TINYINT(1) NOT NULL DEFAULT 0,
  hide_supplier_identity TINYINT(1) NOT NULL DEFAULT 1,
  image_path VARCHAR(255),
  datasheet_path VARCHAR(255),
  status ENUM('Draft','Published','Inactive') NOT NULL DEFAULT 'Draft',
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS staff_permissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  staff_id INT NOT NULL,
  module_name VARCHAR(120) NOT NULL,
  can_view TINYINT(1) NOT NULL DEFAULT 0,
  can_create TINYINT(1) NOT NULL DEFAULT 0,
  can_update TINYINT(1) NOT NULL DEFAULT 0,
  can_delete TINYINT(1) NOT NULL DEFAULT 0,
  can_approve TINYINT(1) NOT NULL DEFAULT 0,
  granted_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_staff_module (staff_id, module_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS technical_articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(260) NOT NULL UNIQUE,
  category VARCHAR(160),
  excerpt VARCHAR(320),
  content MEDIUMTEXT,
  seo_title VARCHAR(255),
  meta_description VARCHAR(320),
  keywords TEXT,
  related_brand VARCHAR(160),
  related_industry VARCHAR(160),
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS activity_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  actor_type ENUM('Customer','Staff','System') NOT NULL DEFAULT 'System',
  actor_id INT NULL,
  action VARCHAR(160) NOT NULL,
  entity_type VARCHAR(80),
  entity_id INT NULL,
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO categories (name, slug, sort_order) VALUES
('Phoenix Contact Products','phoenix-contact-products',1),('Gaskets & Sealing','gaskets-sealing',2),('Chemical Supply','chemical-supply',3),('Raw Materials','raw-materials',4),
('Safety & PPE','safety-ppe',5),('Mechanical & Piping','mechanical-piping',6),('Filtration & Separation','filtration-separation',7),('Saudi Manufacturers','saudi-manufacturers',8),
('Project Procurement','project-procurement',9),('Logistics Support','logistics-support',10);

INSERT IGNORE INTO staff_users (name, email, mobile, password_hash, role)
VALUES ('Phoenix Super Admin','superadmin@phoenix.com.sa','966533033352','$2y$10$B9QG0UoQb6X6vMeLR0nJCuB8dP1LuzMdO3I7vkFMxgVRuVT6mYb7u','Super Admin');

INSERT IGNORE INTO staff_permissions
(staff_id, module_name, can_view, can_create, can_update, can_delete, can_approve, granted_by)
SELECT id, 'suppliers', 1, 1, 1, 1, 1, id FROM staff_users WHERE role = 'Super Admin';

INSERT IGNORE INTO staff_permissions
(staff_id, module_name, can_view, can_create, can_update, can_delete, can_approve, granted_by)
SELECT id, 'supplier_products', 1, 1, 1, 1, 1, id FROM staff_users WHERE role = 'Super Admin';

INSERT IGNORE INTO staff_permissions
(staff_id, module_name, can_view, can_create, can_update, can_delete, can_approve, granted_by)
SELECT id, 'staff_permissions', 1, 1, 1, 1, 1, id FROM staff_users WHERE role = 'Super Admin';

INSERT IGNORE INTO brands (brand_name, slug, public_description, seo_title, meta_description) VALUES
('Phoenix Contact','phoenix-contact','Phoenix Contact industrial electrical and automation products supplied through Phoenix Arabia RFQ Desk.','Phoenix Contact Supplier in Saudi Arabia | Phoenix Arabia','Request Phoenix Contact industrial automation products in Saudi Arabia through Phoenix Arabia.'),
('Phoenix Arabia','phoenix-arabia','Phoenix Arabia industrial supply, procurement and RFQ marketplace.','Phoenix Arabia Industrial Supply Marketplace','Industrial RFQ marketplace for EPC contractors, factories and industrial buyers in Saudi Arabia.'),
('Protected Supplier Network','protected-supplier-network','Selected manufacturers and suppliers represented commercially through Phoenix Arabia.','Industrial Supplier Network Saudi Arabia | Phoenix Arabia','Protected supplier network for industrial products and project procurement.');

INSERT IGNORE INTO industry_pages (industry_name, slug, headline, intro, seo_title, meta_description, keywords) VALUES
('Oil & Gas','oil-gas','Industrial Supply for Oil & Gas Projects','Phoenix Arabia supports oil and gas projects with RFQ based supply, chemicals, materials, mechanical products and project procurement.','Oil and Gas Industrial Supplier Saudi Arabia | Phoenix Arabia','Industrial products, chemicals, materials and procurement support for oil and gas projects in Saudi Arabia.','oil and gas supplier Saudi Arabia, Aramco project materials, EPC procurement'),
('EPC Contractors','epc-contractors','Procurement Support for EPC Contractors','Phoenix Arabia supports EPC contractors with RFQ consolidation, product sourcing, BOQ and MTO pricing.','EPC Procurement Support Saudi Arabia | Phoenix Arabia','RFQ and procurement support for EPC contractors in Saudi Arabia.','EPC supplier Saudi Arabia, RFQ support, BOQ pricing'),
('Industrial Automation','industrial-automation','Industrial Automation Products and RFQ Support','Phoenix Arabia supplies automation components, electrical products and industrial systems through RFQ workflow.','Industrial Automation Supplier Saudi Arabia | Phoenix Arabia','Request industrial automation and electrical products in Saudi Arabia through Phoenix Arabia.','industrial automation Saudi Arabia, Phoenix Contact supplier'),
('Water Treatment','water-treatment','Water Treatment and Industrial Chemical Supply','Phoenix Arabia supports water treatment facilities with chemical supply, filtration and project procurement.','Water Treatment Chemical Supplier Saudi Arabia | Phoenix Arabia','Water treatment chemicals and filtration products supplied through Phoenix Arabia.','water treatment chemicals Saudi Arabia, filtration supplier'),
('Project Logistics','project-logistics','Project Logistics and Supply Chain Coordination','Phoenix Arabia supports projects with logistics coordination, import support, transportation and delivery follow up.','Project Logistics Support Saudi Arabia | Phoenix Arabia','Project logistics, import coordination and supply chain support in Saudi Arabia.','project logistics Saudi Arabia, industrial supply chain');

INSERT INTO products
(name, slug, brand, category, sku, price_mode, unit, short_description, description, stock_status, is_featured, manufacturer_visible, manufacturer_name, internal_supplier_name, seo_title, meta_description, keywords)
VALUES
('Industrial Electrical & Automation Components','industrial-electrical-automation-components','Phoenix Contact','Phoenix Contact Products','PHX-CONTACT-RFQ','Request Price','Lot','Terminal blocks, power supplies, relays, surge protection and automation components.','Phoenix Contact industrial electrical and automation products supplied through Phoenix Arabia RFQ Desk.','Upon Request',1,1,'Phoenix Contact','Phoenix Contact','Phoenix Contact Supplier in Saudi Arabia | Phoenix Arabia','Request Phoenix Contact industrial electrical and automation products in Saudi Arabia through Phoenix Arabia RFQ Desk.','Phoenix Contact Saudi Arabia, terminal blocks, industrial automation, EPC supplier'),
('Industrial Gaskets and Sealing Products','industrial-gaskets-sealing-products','Phoenix Arabia','Gaskets & Sealing','PA-GASKETS-MTO','MTO','Set','Gaskets by size, material, rating, standard and project requirement.','Industrial gaskets and sealing products priced after MTO or RFQ review.','MTO',1,0,NULL,'Iman Gaskets / Protected Supplier','Industrial Gaskets Supplier Saudi Arabia | Phoenix Arabia','Request industrial gaskets, sealing products and MTO pricing in Saudi Arabia through Phoenix Arabia.','gaskets Saudi Arabia, sealing products, MTO gasket supplier'),
('Industrial Chemicals & Oilfield Chemicals','industrial-chemicals-oilfield-chemicals','Phoenix Arabia','Chemical Supply','PA-CHEM-RFQ','RFQ Only','Lot','Chemical sourcing, production chemicals, water treatment and project chemical supply.','Industrial and oilfield chemical supply managed through Phoenix Arabia RFQ Desk.','Upon Request',1,0,NULL,'AST Group / Protected Supplier','Industrial Chemical Supplier Saudi Arabia | Phoenix Arabia','RFQ support for industrial chemicals, oilfield chemicals and water treatment chemicals in Saudi Arabia.','industrial chemicals Saudi Arabia, oilfield chemicals, water treatment chemicals'),
('Safety Supplies and PPE','safety-supplies-ppe','Phoenix Arabia','Safety & PPE','PA-PPE-RFQ','Request Price','Lot','Helmets, gloves, shoes, coveralls and site safety consumables.','Safety supplies and PPE for industrial sites and projects.','Upon Request',0,0,NULL,'Protected Supplier Network','Safety PPE Supplier Saudi Arabia | Phoenix Arabia','Request PPE and safety supplies for industrial projects in Saudi Arabia.','PPE supplier Saudi Arabia, safety supplies, industrial safety'),
('Couplings, Fittings, Valves and Supports','couplings-fittings-valves-supports','Phoenix Arabia','Mechanical & Piping','PA-MECH-RFQ','Request Price','Lot','Mechanical project materials for EPC, HVAC, fire protection, water and oil and gas.','Mechanical and piping materials supplied through Phoenix Arabia.','Upon Request',0,0,NULL,'Protected Supplier Network','Mechanical and Piping Supplier Saudi Arabia | Phoenix Arabia','Request couplings, fittings, valves and pipe supports for EPC projects in Saudi Arabia.','couplings Saudi Arabia, pipe supports, valves supplier'),
('Filtration and Separation Packages','filtration-separation-packages','Phoenix Arabia','Filtration & Separation','PA-FILTRATION-RFQ','RFQ Only','Package','Liquid liquid, solid liquid, gas liquid and hydrocarbon recovery solutions.','Advanced filtration and separation package solutions subject to technical review.','Upon Request',1,0,NULL,'AST Group / Protected Supplier','Filtration and Separation Solutions Saudi Arabia | Phoenix Arabia','Request filtration and separation packages for oil gas and industrial projects.','filtration Saudi Arabia, separation packages, hydrocarbon recovery'),
('Industrial Raw Materials','industrial-raw-materials','Phoenix Arabia','Raw Materials','PA-RAW-MTO','MTO','Ton','Bulk raw materials sourced from Saudi and international manufacturers.','Raw material sourcing through Phoenix Arabia protected commercial channel.','MTO',0,0,NULL,'Protected Supplier Network','Industrial Raw Materials Supplier Saudi Arabia | Phoenix Arabia','Request bulk industrial raw materials from Saudi and international manufacturers through Phoenix Arabia.','raw materials Saudi Arabia, industrial materials, bulk supply'),
('Saudi Factory Product Catalogue','saudi-factory-product-catalogue','Phoenix Arabia','Saudi Manufacturers','PA-KSA-MFG-RFQ','RFQ Only','Lot','Selected products from Saudi factories represented commercially through Phoenix Arabia.','Saudi manufacturer products listed through Phoenix Arabia commercial channel.','Upon Request',1,0,NULL,'Saudi Manufacturers Network','Saudi Manufacturers Product Catalogue | Phoenix Arabia','Saudi factory product catalogue represented commercially through Phoenix Arabia.','Saudi manufacturers, local products, industrial supplier');

INSERT IGNORE INTO technical_articles (title, slug, category, excerpt, content, seo_title, meta_description, keywords, related_brand, related_industry) VALUES
('How Industrial RFQ Procurement Works in Saudi Arabia','industrial-rfq-procurement-saudi-arabia','Procurement','A practical overview of RFQ based procurement for industrial products and EPC projects.','Industrial RFQ procurement relies on BOQ, MTO, technical datasheets, lead time validation and supplier comparison. Phoenix Arabia centralizes this process through one commercial RFQ Desk.','Industrial RFQ Procurement Saudi Arabia | Phoenix Arabia','Learn how industrial RFQ procurement works for EPC and industrial projects in Saudi Arabia.','RFQ Saudi Arabia, EPC procurement, BOQ pricing','Phoenix Arabia','EPC Contractors'),
('Phoenix Contact Products for Industrial Automation Projects','phoenix-contact-industrial-automation-projects','Automation','Phoenix Contact products are commonly used in automation, electrical panels and industrial systems.','Phoenix Contact products support industrial automation through terminal blocks, power supplies, relays, surge protection and networking components. Phoenix Arabia supports RFQ and procurement for these products.','Phoenix Contact Industrial Automation Saudi Arabia | Phoenix Arabia','Request Phoenix Contact industrial automation products through Phoenix Arabia RFQ Desk.','Phoenix Contact, industrial automation, electrical components','Phoenix Contact','Industrial Automation'),
('Why MTO Pricing Matters for Industrial Gaskets','mto-pricing-industrial-gaskets','Gaskets','Gaskets are often priced based on size, material, pressure rating and project requirement.','Industrial gasket pricing often depends on MTO details such as standard, size, pressure class, material, quantity and delivery schedule. Phoenix Arabia supports MTO review and supplier coordination.','MTO Pricing for Industrial Gaskets Saudi Arabia | Phoenix Arabia','Understand MTO pricing for industrial gaskets and sealing products in Saudi Arabia.','gaskets MTO, industrial gaskets, sealing products','Phoenix Arabia','Oil & Gas');
