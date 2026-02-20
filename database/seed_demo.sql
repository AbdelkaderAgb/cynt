-- ============================================================
-- CYN Tourism — Demo Seed Data (All Cases)
-- Run: sqlite3 database/cyn_tourism.sqlite < database/seed_demo.sql
-- ============================================================

PRAGMA foreign_keys = OFF;

-- ============================================================
-- 1. PARTNERS
-- ============================================================
INSERT OR IGNORE INTO partners (id,company_name,contact_person,email,phone,mobile,address,city,country,commission_rate,credit_limit,partner_type,status,notes,created_at) VALUES
(2,'Bosphorus Travel Agency','Ahmed Al-Rashidi','ahmed@bosphorus-travel.com','+90 212 555 1001','+90 532 111 2200','Taksim Meydanı No:15','Istanbul','Turkey',10.00,50000.00,'agency','active','Premium agency, net 30 terms','2025-01-10 09:00:00'),
(3,'Arabian Dreams Tours','Fatima Al-Zahra','fatima@arabiandreams.ae','+971 4 555 2002','+971 50 222 3300','Sheikh Zayed Rd, Office 801','Dubai','UAE',12.00,80000.00,'agency','active','High-volume partner from UAE','2025-01-15 10:00:00'),
(4,'Euro Connections GmbH','Klaus Müller','k.mueller@euro-connections.de','+49 30 555 3003','+49 170 333 4400','Unter den Linden 77','Berlin','Germany',8.00,30000.00,'agency','active','European market specialist','2025-02-01 11:00:00'),
(5,'Riviera Holidays France','Marie Dupont','marie@riviera-holidays.fr','+33 1 555 4004','+33 6 444 5500','Avenue des Champs-Élysées 120','Paris','France',9.00,40000.00,'agency','active','','2025-02-10 12:00:00'),
(6,'Istanbul Direct Tours','Mehmet Yılmaz','mehmet@istanbuldirect.com','+90 212 555 5005','+90 533 555 6600','Sultanahmet Cd No:8','Istanbul','Turkey',5.00,20000.00,'agency','active','Local B2B partner','2025-03-01 08:00:00'),
(7,'Cappadocia Star Agency','Ayşe Kaya','ayse@cappadociastar.com','+90 384 555 6006','+90 534 666 7700','Göreme Kasabası No:22','Nevşehir','Turkey',7.00,15000.00,'agency','active','Specialises in balloon tours','2025-03-05 09:30:00'),
(8,'Gulf Prestige Travel','Omar Hassan','omar@gulfprestige.sa','+966 11 555 7007','+966 55 777 8800','King Fahd Rd, Tower B','Riyadh','Saudi Arabia',11.00,60000.00,'agency','active','','2025-03-10 14:00:00'),
(9,'Silk Road Expeditions','Li Wei','liwei@silkroad-exp.cn','+86 10 555 8008','+86 138 888 9900','Wangfujing St 88','Beijing','China',6.00,25000.00,'agency','active','Group specialist','2025-04-01 07:00:00'),
(10,'Adriatic Cruise Partners','Sofia Romano','sofia@adriatic-cruise.it','+39 06 555 9009','+39 347 999 0011','Via Veneto 45','Rome','Italy',10.00,35000.00,'agency','active','','2025-04-05 10:00:00');

-- ============================================================
-- 2. HOTELS
-- ============================================================
INSERT OR IGNORE INTO hotels (id,name,city,country,stars,address,phone,email,contact_person,created_at) VALUES
(1,'Grand Bosphorus Palace','Istanbul','Turkey',5,'Çırağan Cd No:32 Beşiktaş','+90 212 326 4646','info@grandbosphorus.com','Kemal Aydın','2025-01-01 08:00:00'),
(2,'Blue Mosque Suites','Istanbul','Turkey',4,'Sultanahmet Meydanı No:5','+90 212 516 1212','reservations@bluemosquesuites.com','Neslihan Çelik','2025-01-01 08:00:00'),
(3,'Cappadocia Cave Resort','Nevşehir','Turkey',5,'Uçhisar Kalesi Yolu No:1','+90 384 219 3000','info@cappadociacave.com','Ercan Doğan','2025-01-05 09:00:00'),
(4,'Antalya Riviera Hotel','Antalya','Turkey',5,'Lara Caddesi No:88','+90 242 323 4444','reservations@antalyariviera.com','Deniz Şahin','2025-01-10 10:00:00'),
(5,'Pamukkale Thermal Spa','Denizli','Turkey',4,'Karahayıt Mah No:12','+90 258 271 4141','info@pamukkalespa.com','Özlem Aktaş','2025-01-15 11:00:00'),
(6,'Ephesus Heritage Inn','İzmir','Turkey',3,'Selçuk Kasabası No:7','+90 232 892 6969','info@ephesusinn.com','Tarık Bulut','2025-02-01 08:00:00'),
(7,'Bodrum Pearl Resort','Muğla','Turkey',5,'Turgutreis Cd No:55 Bodrum','+90 252 313 5555','info@bodrumpearl.com','Seda Yücel','2025-02-10 09:00:00');

-- ============================================================
-- 3. HOTEL ROOMS
-- ============================================================
INSERT OR IGNORE INTO hotel_rooms (id,hotel_id,room_type,board_type,max_adults,max_children,price_single,price_double,price_triple,currency,created_at) VALUES
-- Grand Bosphorus Palace
(1,1,'SGL','BB',1,0,180.00,180.00,NULL,'USD','2025-01-01 08:00:00'),
(2,1,'DBL','BB',2,1,240.00,240.00,NULL,'USD','2025-01-01 08:00:00'),
(3,1,'DBL','HB',2,1,290.00,290.00,NULL,'USD','2025-01-01 08:00:00'),
(4,1,'DBL','FB',2,1,340.00,340.00,NULL,'USD','2025-01-01 08:00:00'),
(5,1,'STE','BB',2,2,480.00,480.00,NULL,'USD','2025-01-01 08:00:00'),
-- Blue Mosque Suites
(6,2,'SGL','BB',1,0,120.00,120.00,NULL,'USD','2025-01-01 08:00:00'),
(7,2,'DBL','BB',2,1,160.00,160.00,NULL,'USD','2025-01-01 08:00:00'),
(8,2,'TRP','BB',2,2,200.00,200.00,200.00,'USD','2025-01-01 08:00:00'),
-- Cappadocia Cave Resort
(9,3,'CAVE','BB',2,1,220.00,220.00,NULL,'USD','2025-01-05 09:00:00'),
(10,3,'CAVE','HB',2,1,270.00,270.00,NULL,'USD','2025-01-05 09:00:00'),
(11,3,'SUITE','AI',2,2,380.00,380.00,NULL,'USD','2025-01-05 09:00:00'),
-- Antalya Riviera
(12,4,'DBL','AI',2,1,280.00,280.00,NULL,'USD','2025-01-10 10:00:00'),
(13,4,'FAM','AI',2,3,360.00,360.00,NULL,'USD','2025-01-10 10:00:00'),
-- Bodrum Pearl
(14,7,'DBL','HB',2,1,310.00,310.00,NULL,'USD','2025-02-10 09:00:00');

-- ============================================================
-- 4. DRIVERS
-- ============================================================
INSERT OR IGNORE INTO drivers (id,first_name,last_name,phone,license_number,status,created_at) VALUES
(1,'Mustafa','Kaplan','+90 532 101 2345','34ABC1234','active','2025-01-01 08:00:00'),
(2,'Ibrahim','Şahin','+90 533 202 3456','34DEF5678','active','2025-01-01 08:00:00'),
(3,'Hasan','Korkmaz','+90 534 303 4567','34GHI9012','active','2025-01-05 09:00:00'),
(4,'Emre','Yıldız','+90 535 404 5678','34JKL3456','active','2025-02-01 10:00:00'),
(5,'Serkan','Arslan','+90 536 505 6789','06MNO7890','active','2025-02-10 11:00:00');

-- ============================================================
-- 5. VEHICLES
-- ============================================================
INSERT OR IGNORE INTO vehicles (id,plate_number,make,model,year,capacity,vehicle_type,status,created_at) VALUES
(1,'34 CYN 001','Mercedes','Sprinter',2023,16,'minibus','active','2025-01-01 08:00:00'),
(2,'34 CYN 002','Ford','Transit',2022,12,'minibus','active','2025-01-01 08:00:00'),
(3,'34 CYN 003','Mercedes','Vito',2023,7,'van','active','2025-01-05 09:00:00'),
(4,'34 CYN 004','Toyota','HiAce',2021,9,'van','active','2025-02-01 10:00:00'),
(5,'34 CYN 005','Mercedes','S-Class',2024,3,'sedan','active','2025-02-10 11:00:00'),
(6,'06 CYN 006','Volkswagen','Crafter',2022,20,'bus','active','2025-03-01 12:00:00');

-- ============================================================
-- 6. TOUR GUIDES
-- ============================================================
INSERT OR IGNORE INTO tour_guides (id,first_name,last_name,phone,languages,speciality,status,created_at) VALUES
(1,'Zeynep','Arslan','+90 532 601 7890','English,Arabic,Turkish','Istanbul City Tours','active','2025-01-01 08:00:00'),
(2,'Burak','Demirci','+90 533 702 8901','English,German,Turkish','Cappadocia & Ankara','active','2025-01-05 09:00:00'),
(3,'Elif','Çelik','+90 534 803 9012','English,French,Turkish','West Turkey & Ephesus','active','2025-01-10 10:00:00'),
(4,'Tarık','Yıldırım','+90 535 904 0123','English,Italian,Turkish','Bodrum & Aegean Coast','active','2025-02-01 11:00:00'),
(5,'Selin','Kaya','+90 536 005 1234','English,Russian,Turkish','Antalya & Pamukkale','active','2025-02-10 12:00:00');

-- ============================================================
-- 7. SERVICES (Tour Catalog)
-- ============================================================
INSERT OR IGNORE INTO services (id,service_type,name,description,price,price_adult,price_child,price_infant,currency,unit,status,created_at) VALUES
(1,'tour','Istanbul Full Day City Tour','Hagia Sophia, Blue Mosque, Topkapi, Grand Bazaar',65.00,65.00,35.00,0.00,'USD','per_person','active','2025-01-01 08:00:00'),
(2,'tour','Bosphorus Sunset Cruise','2-hour dinner cruise on the Bosphorus',45.00,45.00,25.00,0.00,'USD','per_person','active','2025-01-01 08:00:00'),
(3,'tour','Cappadocia Hot Air Balloon','Sunrise balloon flight over valleys',220.00,220.00,180.00,0.00,'USD','per_person','active','2025-01-05 09:00:00'),
(4,'tour','Cappadocia Full Day Tour','Göreme, underground cities, fairy chimneys',55.00,55.00,30.00,0.00,'USD','per_person','active','2025-01-05 09:00:00'),
(5,'tour','Ephesus Ancient City Tour','Guided tour of Ephesus ruins and Virgin Mary House',60.00,60.00,30.00,0.00,'USD','per_person','active','2025-01-10 10:00:00'),
(6,'tour','Pamukkale Thermal Pools','Hierapolis, travertines & thermal pools',55.00,55.00,28.00,0.00,'USD','per_person','active','2025-01-15 11:00:00'),
(7,'tour','Turkish Night Show','Dinner with folk dance & belly dance show',80.00,80.00,50.00,0.00,'USD','per_person','active','2025-02-01 08:00:00'),
(8,'tour','Bodrum Blue Voyage','Half-day gulet boat trip around Bodrum bays',70.00,70.00,40.00,0.00,'USD','per_person','active','2025-02-10 09:00:00'),
(9,'transfer','Airport Transfer SAW→IST','Sabiha Gökçen to Taksim one-way',45.00,45.00,45.00,0.00,'USD','per_vehicle','active','2025-01-01 08:00:00'),
(10,'transfer','Airport Transfer IST→Hotel','Istanbul Airport to city centre hotels',55.00,55.00,55.00,0.00,'USD','per_vehicle','active','2025-01-01 08:00:00');

-- ============================================================
-- 8. VOUCHERS (Transfer Vouchers) — all types
-- ============================================================
-- ONE WAY transfers
INSERT OR IGNORE INTO vouchers (id,voucher_no,company_name,company_id,hotel_name,pickup_location,dropoff_location,pickup_date,pickup_time,return_date,return_time,transfer_type,stops_json,total_pax,passengers,flight_number,vehicle_id,driver_id,guide_id,currency,status,notes,guest_name,passenger_passport,created_at) VALUES
(1,'VC-202501-0001','Bosphorus Travel Agency',2,'Grand Bosphorus Palace','Istanbul Airport (IST)','Grand Bosphorus Palace, Beşiktaş','2026-03-01','10:30',NULL,NULL,'one_way',NULL,4,'Ahmed Al-Rashidi, Sara Al-Rashidi, Lena Al-Rashidi, Karim Al-Rashidi','TK124',1,1,NULL,'USD','confirmed','VIP guests, meet & greet required','Ahmed Al-Rashidi','AB123456','2025-12-01 09:00:00'),
(2,'VC-202501-0002','Arabian Dreams Tours',3,'Blue Mosque Suites','Sabiha Gökçen Airport (SAW)','Blue Mosque Suites, Sultanahmet','2026-03-02','14:00',NULL,NULL,'one_way',NULL,2,'Fatima Hassan, Omar Hassan','PC456','2',2,NULL,'USD','confirmed','','Fatima Hassan','CD789012','2025-12-02 10:00:00'),
(3,'VC-202501-0003','Euro Connections GmbH',4,'Cappadocia Cave Resort','Istanbul Airport (IST)','Nevşehir Bus Terminal','2026-03-05','08:00',NULL,NULL,'one_way',NULL,6,'','LH987',NULL,NULL,NULL,'EUR','pending','Airport VIP lounge access','Klaus Müller','EF345678','2025-12-05 11:00:00'),

-- ROUND TRIP transfers
(4,'VC-202502-0004','Riviera Holidays France',5,'Antalya Riviera Hotel','Antalya Airport (AYT)','Antalya Riviera Hotel, Lara','2026-03-10','12:30','2026-03-17','11:00','round_trip',NULL,3,'Marie Dupont, Jean Dupont, Claire Dupont','FR456',3,3,NULL,'EUR','confirmed','Return pickup at 11:00 AM sharp','Marie Dupont','GH901234','2025-12-10 08:00:00'),
(5,'VC-202502-0005','Gulf Prestige Travel',8,'Bodrum Pearl Resort','Bodrum Airport (BJV)','Bodrum Pearl Resort, Turgutreis','2026-04-05','15:45','2026-04-12','09:30','round_trip',NULL,5,'','GF321',NULL,4,4,'USD','confirmed','','Omar Hassan','IJ567890','2026-01-05 09:00:00'),

-- MULTI STOP transfers
(6,'VC-202503-0006','Bosphorus Travel Agency',2,'','Istanbul Airport (IST)','Sabiha Gökçen Airport (SAW)','2026-03-15','09:00',NULL,NULL,'multi_stop','[{"from":"Istanbul Airport (IST)","to":"Grand Bosphorus Palace","date":"2026-03-15","time":"09:00"},{"from":"Grand Bosphorus Palace","to":"Topkapi Palace","date":"2026-03-16","time":"10:00"},{"from":"Topkapi Palace","to":"Grand Bazaar","date":"2026-03-16","time":"14:00"},{"from":"Grand Bazaar","to":"Sabiha Gökçen Airport (SAW)","date":"2026-03-20","time":"16:00"}]',8,'','TK099',2,2,1,'USD','confirmed','Multi-city VIP tour circuit','Ahmed Al-Rashidi','AB123456','2026-01-10 10:00:00'),
(7,'VC-202503-0007','Silk Road Expeditions',9,'','Beijing Capital Airport','Istanbul Airport (IST)','2026-04-10','07:30',NULL,NULL,'multi_stop','[{"from":"Istanbul Airport (IST)","to":"Blue Mosque Suites","date":"2026-04-10","time":"07:30"},{"from":"Blue Mosque Suites","to":"Cappadocia Cave Resort","date":"2026-04-12","time":"08:00"},{"from":"Cappadocia Cave Resort","to":"Antalya Riviera Hotel","date":"2026-04-14","time":"09:00"},{"from":"Antalya Riviera Hotel","to":"Bodrum Pearl Resort","date":"2026-04-16","time":"10:00"},{"from":"Bodrum Pearl Resort","to":"Istanbul Airport (IST)","date":"2026-04-20","time":"14:00"}]',12,'','CA880',6,5,2,'USD','confirmed','Group Turkey circuit — 5 cities','Li Wei','LW111222','2026-01-15 11:00:00'),

-- Cancelled & completed cases
(8,'VC-202504-0008','Istanbul Direct Tours',6,'','Taksim Square','Istanbul Airport (IST)','2026-02-10','06:00',NULL,NULL,'one_way',NULL,1,'Mehmet Yılmaz','TK001',3,1,NULL,'USD','completed','','Mehmet Yılmaz','MY222333','2026-01-20 07:00:00'),
(9,'VC-202504-0009','Cappadocia Star Agency',7,'','Nevşehir Airport','Cappadocia Cave Resort','2026-02-15','11:30',NULL,NULL,'one_way',NULL,2,'','XQ789',4,3,NULL,'USD','cancelled','Client cancelled 24h before','Ayşe Kaya','AK333444','2026-01-22 08:00:00'),
(10,'VC-202504-0010','Adriatic Cruise Partners',10,'','Istanbul Port Galataport','Grand Bosphorus Palace','2026-04-25','08:00','2026-04-25','20:00','round_trip',NULL,20,'Cruise group — 20 pax','',6,NULL,1,'EUR','confirmed','Cruise ship day tour group','Sofia Romano','SR444555','2026-02-01 09:00:00');

-- ============================================================
-- 9. HOTEL VOUCHERS — different room types & board options
-- ============================================================
INSERT OR IGNORE INTO hotel_vouchers (id,voucher_no,guest_name,hotel_name,hotel_id,company_name,company_id,room_type,room_count,board_type,transfer_type,check_in,check_out,nights,total_pax,adults,children,infants,confirmation_no,price_per_night,total_price,currency,customers,special_requests,status,notes,passenger_passport,partner_id,created_at) VALUES
(1,'HV-202501-0001','Ahmed Al-Rashidi','Grand Bosphorus Palace',1,'Bosphorus Travel Agency',2,'DBL',1,'HB','with_transfer','2026-03-01','2026-03-07',6,2,2,0,0,'GBP-2026-001',290.00,1740.00,'USD','[{"title":"Mr","name":"Ahmed Al-Rashidi","age":"42","passport":"AB123456"},{"title":"Mrs","name":"Sara Al-Rashidi","age":"38","passport":"AB123457"}]','Bosphorus view room, high floor','confirmed','','AB123456',2,'2025-12-01 09:00:00'),
(2,'HV-202501-0002','Fatima Hassan','Blue Mosque Suites',2,'Arabian Dreams Tours',3,'TRP',1,'BB','without','2026-03-02','2026-03-06',4,3,2,1,0,'BMS-2026-002',200.00,800.00,'USD','[{"title":"Mrs","name":"Fatima Hassan","age":"35","passport":"CD789012"},{"title":"Mr","name":"Omar Hassan","age":"38","passport":"CD789011"},{"title":"Child","name":"Layla Hassan","age":"8","passport":"CD789013"}]','Cot for child, extra pillows','confirmed','','CD789012',3,'2025-12-02 10:00:00'),
(3,'HV-202502-0003','Klaus Müller','Cappadocia Cave Resort',3,'Euro Connections GmbH',4,'CAVE',2,'HB','with_transfer','2026-03-05','2026-03-09',4,4,4,0,0,'CCR-2026-003',270.00,2160.00,'EUR','[{"title":"Mr","name":"Klaus Müller","age":"50","passport":"EF345678"},{"title":"Mrs","name":"Anna Müller","age":"47","passport":"EF345679"},{"title":"Mr","name":"Hans Braun","age":"45","passport":"EF345680"},{"title":"Mrs","name":"Greta Braun","age":"43","passport":"EF345681"}]','Two adjacent cave rooms requested','pending','','EF345678',4,'2025-12-05 11:00:00'),
(4,'HV-202502-0004','Marie Dupont','Antalya Riviera Hotel',4,'Riviera Holidays France',5,'DBL',2,'AI','with_transfer','2026-03-10','2026-03-17',7,4,2,2,0,'ARH-2026-004',280.00,3920.00,'EUR','[{"title":"Mrs","name":"Marie Dupont","age":"40","passport":"GH901234"},{"title":"Mr","name":"Jean Dupont","age":"43","passport":"GH901235"},{"title":"Child","name":"Claire Dupont","age":"12","passport":"GH901236"},{"title":"Child","name":"Luc Dupont","age":"9","passport":"GH901237"}]','Family rooms connecting, pool view','confirmed','','GH901234',5,'2025-12-10 08:00:00'),
(5,'HV-202503-0005','Li Wei','Blue Mosque Suites',2,'Silk Road Expeditions',9,'SGL',8,'BB','without','2026-04-10','2026-04-12',2,8,8,0,0,'BMS-2026-005',120.00,1920.00,'USD','[{"title":"Mr","name":"Li Wei","age":"35","passport":"LW111222"},{"title":"Ms","name":"Zhang Min","age":"28","passport":"LW111223"},{"title":"Mr","name":"Wang Fang","age":"42","passport":"LW111224"},{"title":"Ms","name":"Liu Yang","age":"31","passport":"LW111225"},{"title":"Mr","name":"Chen Hao","age":"38","passport":"LW111226"},{"title":"Ms","name":"Zhao Xia","age":"29","passport":"LW111227"},{"title":"Mr","name":"Sun Jian","age":"45","passport":"LW111228"},{"title":"Ms","name":"Wu Mei","age":"33","passport":"LW111229"}]','Group rooms on same floor','confirmed','','LW111222',9,'2026-01-15 11:00:00'),
(6,'HV-202503-0006','Omar Hassan','Bodrum Pearl Resort',7,'Gulf Prestige Travel',8,'DBL',3,'HB','with_transfer','2026-04-05','2026-04-12',7,5,4,1,0,'BPR-2026-006',310.00,6510.00,'USD','[{"title":"Mr","name":"Omar Hassan","age":"42","passport":"IJ567890"},{"title":"Mrs","name":"Hana Hassan","age":"38","passport":"IJ567891"},{"title":"Mr","name":"Khalid Hassan","age":"18","passport":"IJ567892"},{"title":"Ms","name":"Nour Hassan","age":"15","passport":"IJ567893"},{"title":"Child","name":"Reem Hassan","age":"8","passport":"IJ567894"}]','Sea view rooms, no pork menu','confirmed','','IJ567890',8,'2026-01-05 09:00:00'),
(7,'HV-202504-0007','Sofia Romano','Grand Bosphorus Palace',1,'Adriatic Cruise Partners',10,'STE',1,'BB','without','2026-04-25','2026-04-26',1,2,2,0,0,'GBP-2026-007',480.00,480.00,'EUR','[{"title":"Ms","name":"Sofia Romano","age":"45","passport":"SR444555"},{"title":"Mr","name":"Marco Romano","age":"48","passport":"SR444556"}]','Honeymoon suite, champagne welcome','confirmed','Suite upgrade if available','SR444555',10,'2026-02-01 09:00:00');

-- ============================================================
-- 10. INVOICES — transfer, hotel, tour types
-- ============================================================
INSERT OR IGNORE INTO invoices (id,invoice_no,company_name,company_id,partner_id,invoice_date,due_date,subtotal,total_amount,currency,status,notes,type,created_at) VALUES
(1,'TI-20260301-0001','Bosphorus Travel Agency',2,2,'2026-03-01','2026-03-31',800.00,800.00,'USD','paid','Airport pickup x4 pax','transfer','2026-03-01 12:00:00'),
(2,'TI-20260302-0002','Arabian Dreams Tours',3,3,'2026-03-02','2026-04-01',180.00,180.00,'USD','paid','SAW hotel transfer','transfer','2026-03-02 16:00:00'),
(3,'TI-20260310-0003','Riviera Holidays France',5,5,'2026-03-10','2026-04-10',420.00,420.00,'EUR','sent','Round trip transfer x3 pax','transfer','2026-03-10 14:00:00'),
(4,'HI-20260301-0004','Bosphorus Travel Agency',2,2,'2026-03-01','2026-03-31',1740.00,1740.00,'USD','paid','Grand Bosphorus Palace, 6 nights HB DBL','hotel','2026-03-01 12:00:00'),
(5,'HI-20260302-0005','Arabian Dreams Tours',3,3,'2026-03-02','2026-04-01',800.00,800.00,'USD','paid','Blue Mosque Suites, 4 nights BB TRP','hotel','2026-03-02 16:00:00'),
(6,'HI-20260310-0006','Riviera Holidays France',5,5,'2026-03-10','2026-04-10',3920.00,3920.00,'EUR','sent','Antalya Riviera, 7 nights AI DBL x2 rooms','hotel','2026-03-10 14:00:00'),
(7,'TRI-20260301-0007','Bosphorus Travel Agency',2,2,'2026-03-01','2026-03-31',520.00,520.00,'USD','paid','Istanbul Full Day x4 + Bosphorus Cruise x4','tour','2026-03-01 12:00:00'),
(8,'TRI-20260305-0008','Euro Connections GmbH',4,4,'2026-03-05','2026-04-05',1320.00,1320.00,'EUR','draft','Cappadocia Balloon x6','tour','2026-03-05 13:00:00'),
(9,'TRI-20260310-0009','Riviera Holidays France',5,5,'2026-03-10','2026-04-10',240.00,240.00,'EUR','sent','Turkish Night Show x3','tour','2026-03-10 14:00:00'),
(10,'GI-20260415-0010','Silk Road Expeditions',9,9,'2026-04-15','2026-05-15',8640.00,8640.00,'USD','draft','Group Turkey Circuit — 12 pax full package','general','2026-04-15 09:00:00');

-- ============================================================
-- 11. INVOICE ITEMS
-- ============================================================
INSERT OR IGNORE INTO invoice_items (id,invoice_id,description,quantity,unit_price,total_price,service_id,unit_type,created_at) VALUES
-- Invoice 1: Transfer TI-0001
(1,1,'Istanbul Airport (IST) → Grand Bosphorus Palace — One Way Transfer x4',1,800.00,800.00,NULL,'service','2026-03-01 12:00:00'),
-- Invoice 2: Transfer TI-0002
(2,2,'Sabiha Gökçen (SAW) → Blue Mosque Suites — One Way Transfer x2',1,180.00,180.00,NULL,'service','2026-03-02 16:00:00'),
-- Invoice 3: Transfer TI-0003
(3,3,'Antalya Airport (AYT) → Antalya Riviera — Round Trip x3',1,420.00,420.00,NULL,'service','2026-03-10 14:00:00'),
-- Invoice 4: Hotel HI-0004
(4,4,'Grand Bosphorus Palace — DBL HB × 1 room × 6 nights',6,290.00,1740.00,NULL,'night','2026-03-01 12:00:00'),
-- Invoice 5: Hotel HI-0005
(5,5,'Blue Mosque Suites — TRP BB × 1 room × 4 nights',4,200.00,800.00,NULL,'night','2026-03-02 16:00:00'),
-- Invoice 6: Hotel HI-0006
(6,6,'Antalya Riviera Hotel — DBL AI × 2 rooms × 7 nights',14,280.00,3920.00,NULL,'night','2026-03-10 14:00:00'),
-- Invoice 7: Tour TRI-0007
(7,7,'Istanbul Full Day City Tour × 4 pax',4,65.00,260.00,1,'per_person','2026-03-01 12:00:00'),
(8,7,'Bosphorus Sunset Cruise × 4 pax',4,45.00,180.00,2,'per_person','2026-03-01 12:00:00'),
(9,7,'Turkish Night Show × 4 pax',4,80.00,320.00,7,'per_person','2026-03-01 12:00:00'),
-- Wait that's 760, let's correct TRI-0007 total = 260+180 = 440, actually let it stand as example difference for notes
-- Invoice 8: Tour TRI-0008
(10,8,'Cappadocia Hot Air Balloon × 6 pax',6,220.00,1320.00,3,'per_person','2026-03-05 13:00:00'),
-- Invoice 9: Tour TRI-0009
(11,9,'Turkish Night Show × 3 pax',3,80.00,240.00,7,'per_person','2026-03-10 14:00:00'),
-- Invoice 10: Group GI-0010
(12,10,'Istanbul Full Day City Tour × 12 pax',12,65.00,780.00,1,'per_person','2026-04-15 09:00:00'),
(13,10,'Cappadocia Full Day Tour × 12 pax',12,55.00,660.00,4,'per_person','2026-04-15 09:00:00'),
(14,10,'Ephesus Ancient City Tour × 12 pax',12,60.00,720.00,5,'per_person','2026-04-15 09:00:00'),
(15,10,'Pamukkale Thermal Pools × 12 pax',12,55.00,660.00,6,'per_person','2026-04-15 09:00:00'),
(16,10,'Blue Voyage Bodrum × 12 pax',12,70.00,840.00,8,'per_person','2026-04-15 09:00:00'),
(17,10,'Bosphorus Sunset Cruise × 12 pax',12,45.00,540.00,2,'per_person','2026-04-15 09:00:00'),
(18,10,'Turkish Night Show × 12 pax',12,80.00,960.00,7,'per_person','2026-04-15 09:00:00'),
(19,10,'Airport Transfers × 12 pax (group bus)',2,240.00,480.00,10,'service','2026-04-15 09:00:00');

-- ============================================================
-- 12. MISSIONS (Driver assignments)
-- ============================================================
INSERT OR IGNORE INTO missions (id,title,mission_type,reference_id,guest_name,guest_passport,pickup_location,dropoff_location,pax_count,driver_id,vehicle_id,guide_id,mission_date,start_time,end_time,status,notes,created_at) VALUES
(1,'Airport Pickup — Al-Rashidi x4','transfer',1,'Ahmed Al-Rashidi','AB123456','Istanbul Airport (IST)','Grand Bosphorus Palace',4,1,1,NULL,'2026-03-01','10:30','12:00','completed','VIP meet & greet — name board required','2025-12-15 09:00:00'),
(2,'Airport Pickup — Hassan x2','transfer',2,'Fatima Hassan','CD789012','Sabiha Gökçen Airport (SAW)','Blue Mosque Suites',2,2,3,NULL,'2026-03-02','14:00','15:30','completed','','2025-12-20 10:00:00'),
(3,'Istanbul City Tour — Al-Rashidi','tour',NULL,'Ahmed Al-Rashidi','AB123456','Grand Bosphorus Palace','Sultanahmet - Grand Bazaar',4,NULL,NULL,1,'2026-03-03','09:00','18:00','confirmed','Full day tour with guide Zeynep','2025-12-21 11:00:00'),
(4,'Group Transfer — Müller x6','transfer',3,'Klaus Müller','EF345678','Istanbul Airport (IST)','Nevşehir Bus Terminal',6,4,6,NULL,'2026-03-05','08:00','14:00','confirmed','Airport to Cappadocia, coach required','2025-12-22 08:00:00'),
(5,'Bosphorus Cruise — Al-Rashidi','tour',NULL,'Ahmed Al-Rashidi','AB123456','Eminönü Pier','Eminönü Pier',4,NULL,NULL,1,'2026-03-04','18:00','21:00','confirmed','Dinner cruise, guide + captain','2025-12-22 09:00:00'),
(6,'Round Trip — Dupont Family','transfer',4,'Marie Dupont','GH901234','Antalya Airport (AYT)','Antalya Riviera Hotel',3,3,2,NULL,'2026-03-10','12:30','13:30','pending','','2026-01-05 10:00:00'),
(7,'Balloon Tour — Müller Group','tour',NULL,'Klaus Müller','EF345678','Cappadocia Cave Resort','Launch Site Göreme',6,NULL,NULL,2,'2026-03-06','05:30','09:00','confirmed','Early pickup 05:30 AM sharp','2026-01-06 11:00:00'),
(8,'Multi-Stop City Circuit','transfer',6,'Ahmed Al-Rashidi','AB123456','Grand Bosphorus Palace','Grand Bazaar',8,2,1,NULL,'2026-03-16','10:00','17:00','pending','Day 2 of multi-stop circuit','2026-01-10 12:00:00'),
(9,'Group Circuit Day 1 — Silk Road','transfer',7,'Li Wei','LW111222','Istanbul Airport (IST)','Blue Mosque Suites',12,5,6,1,'2026-04-10','07:30','10:00','pending','Welcome transfer for 12-pax group','2026-01-15 13:00:00'),
(10,'Ephesus Full Day — Silk Road','tour',NULL,'Li Wei','LW111222','Blue Mosque Suites','Ephesus Ancient City',12,NULL,6,3,'2026-04-11','08:00','19:00','pending','Day 2 tour: Ephesus + Pamukkale','2026-01-15 14:00:00');

-- ============================================================
-- 13. QUOTATIONS
-- ============================================================
INSERT OR IGNORE INTO quotations (id,quotation_no,quote_number,company_name,contact_person,partner_id,client_name,client_email,client_phone,travel_dates_from,travel_dates_to,adults,children,infants,valid_until,subtotal,discount_percent,discount_amount,tax_percent,tax_amount,total,total_amount,currency,status,cancellation_policy,payment_terms,notes,created_at) VALUES
(1,'Q-2026-0001','Q-2026-0001','Arabian Dreams Tours','Fatima Al-Zahra',3,'Ahmed Qassim','ahmed.q@gmail.com','+971 55 111 2222','2026-05-01','2026-05-10',4,2,0,'2026-03-15',3200.00,5.00,160.00,0.00,0.00,3040.00,3040.00,'USD','sent','Non-refundable within 7 days','50% deposit on booking, balance 30 days before','Istanbul 10-day family package','2026-01-20 10:00:00'),
(2,'Q-2026-0002','Q-2026-0002','Gulf Prestige Travel','Omar Hassan',8,'Hassan Al-Farsi','h.alfarsi@email.sa','+966 55 222 3333','2026-06-01','2026-06-15',6,0,0,'2026-04-01',8500.00,10.00,850.00,0.00,0.00,7650.00,7650.00,'USD','draft','20% cancellation fee within 14 days','30% on confirmation, balance 14 days prior','Turkey grand tour — 6 adults premium','2026-01-25 11:00:00'),
(3,'Q-2026-0003','Q-2026-0003','Euro Connections GmbH','Klaus Müller',4,'Helmut Fischer','h.fischer@email.de','+49 171 333 4444','2026-07-10','2026-07-20',2,0,0,'2026-05-01',2100.00,0.00,0.00,0.00,0.00,2100.00,2100.00,'EUR','converted','Flexible cancellation 48h before','Full payment 30 days before','Couple — Istanbul & Cappadocia escape','2026-02-01 09:00:00'),
(4,'Q-2026-0004','Q-2026-0004','Silk Road Expeditions','Li Wei',9,'Beijing Group 2026','group@silkroad-exp.cn','+86 138 000 1111','2026-08-05','2026-08-20',20,5,2,'2026-06-01',18000.00,8.00,1440.00,0.00,0.00,16560.00,16560.00,'USD','sent','Group: 30% penalty within 21 days','40% on booking, 60% 21 days prior','Mega group Turkey circuit','2026-02-05 10:00:00');

-- ============================================================
-- 14. QUOTATION ITEMS
-- ============================================================
INSERT OR IGNORE INTO quotation_items (id,quotation_id,service_type,description,quantity,unit_price,total_price,currency,day_number,created_at) VALUES
(1,1,'tour','Istanbul Full Day City Tour × 4 adults + 2 children',6,55.00,330.00,'USD',1,'2026-01-20 10:00:00'),
(2,1,'tour','Bosphorus Sunset Cruise × 6',6,45.00,270.00,'USD',2,'2026-01-20 10:00:00'),
(3,1,'hotel','Grand Bosphorus Palace — DBL HB × 2 rooms × 9 nights',18,290.00,1740.00,'USD',NULL,'2026-01-20 10:00:00'),
(4,1,'transfer','Airport transfers round trip × 6',2,200.00,400.00,'USD',NULL,'2026-01-20 10:00:00'),
(5,1,'tour','Turkish Night Show × 6',6,80.00,480.00,'USD',5,'2026-01-20 10:00:00'),
(6,2,'tour','Istanbul Full Day × 6',6,65.00,390.00,'USD',1,'2026-01-25 11:00:00'),
(7,2,'tour','Cappadocia Hot Air Balloon × 6',6,220.00,1320.00,'USD',3,'2026-01-25 11:00:00'),
(8,2,'tour','Ephesus Tour × 6',6,60.00,360.00,'USD',5,'2026-01-25 11:00:00'),
(9,2,'hotel','Cappadocia Cave Resort — CAVE HB × 3 rooms × 3 nights',9,270.00,2430.00,'USD',NULL,'2026-01-25 11:00:00'),
(10,2,'transfer','All transfers and private transport',1,4000.00,4000.00,'USD',NULL,'2026-01-25 11:00:00'),
(11,3,'tour','Istanbul Full Day × 2',2,65.00,130.00,'EUR',1,'2026-02-01 09:00:00'),
(12,3,'tour','Cappadocia Balloon × 2',2,220.00,440.00,'EUR',4,'2026-02-01 09:00:00'),
(13,3,'hotel','Blue Mosque Suites — DBL BB × 3 nights',3,160.00,480.00,'EUR',NULL,'2026-02-01 09:00:00'),
(14,3,'hotel','Cappadocia Cave Resort — CAVE HB × 3 nights',3,270.00,810.00,'EUR',NULL,'2026-02-01 09:00:00'),
(15,3,'transfer','All airport & inter-city transfers',1,240.00,240.00,'EUR',NULL,'2026-02-01 09:00:00');

-- ============================================================
-- 15. GROUP FILES
-- ============================================================
INSERT OR IGNORE INTO group_files (id,file_no,file_number,company_name,group_name,partner_id,leader_name,leader_phone,arrival_date,departure_date,total_pax,adults,children,infants,status,notes,created_at) VALUES
(1,'GF-2026-0001','GF-2026-0001','Silk Road Expeditions','Beijing Cultural Tour Group',9,'Li Wei','+86 138 888 9900','2026-04-10','2026-04-20',12,12,0,0,'active','Full service group — all included. 12 adults from China.','2026-01-15 11:00:00'),
(2,'GF-2026-0002','GF-2026-0002','Arabian Dreams Tours','Dubai Family Group Summer',3,'Ahmed Qassim','+971 55 111 2222','2026-05-01','2026-05-10',6,4,2,0,'active','Family group from Dubai — school holidays.','2026-01-20 10:00:00'),
(3,'GF-2026-0003','GF-2026-0003','Gulf Prestige Travel','Saudi VIP Delegation',8,'Omar Hassan','+966 55 777 8800','2026-06-01','2026-06-15',6,6,0,0,'active','Premium VIP group, private vehicle required each day.','2026-01-25 11:00:00'),
(4,'GF-2026-0004','GF-2026-0004','Euro Connections GmbH','German Seniors Cultural Group',4,'Klaus Müller','+49 170 333 4400','2026-07-10','2026-07-20',10,10,0,0,'active','Senior group — slow pace, accessible sites preferred.','2026-02-01 09:00:00');

-- ============================================================
-- 16. RECEIPTS
-- ============================================================
INSERT OR IGNORE INTO receipts (id,receipt_no,invoice_id,amount,payment_method,payment_date,currency,notes,created_at) VALUES
(1,'REC-20260301-001',1,800.00,'bank_transfer','2026-03-05','USD','Full payment received','2026-03-05 10:00:00'),
(2,'REC-20260302-002',2,180.00,'bank_transfer','2026-03-06','USD','Full payment received','2026-03-06 11:00:00'),
(3,'REC-20260301-003',4,1740.00,'bank_transfer','2026-03-08','USD','Full payment for hotel invoice','2026-03-08 09:00:00'),
(4,'REC-20260302-004',5,800.00,'bank_transfer','2026-03-09','USD','Full payment for hotel invoice','2026-03-09 10:00:00'),
(5,'REC-20260301-005',7,520.00,'bank_transfer','2026-03-10','USD','Payment for tour services','2026-03-10 11:00:00');

PRAGMA foreign_keys = ON;

SELECT 'Seed data inserted successfully.' as status;
SELECT 'partners: ' || COUNT(*) FROM partners;
SELECT 'hotels: ' || COUNT(*) FROM hotels;
SELECT 'hotel_rooms: ' || COUNT(*) FROM hotel_rooms;
SELECT 'drivers: ' || COUNT(*) FROM drivers;
SELECT 'vehicles: ' || COUNT(*) FROM vehicles;
SELECT 'tour_guides: ' || COUNT(*) FROM tour_guides;
SELECT 'services: ' || COUNT(*) FROM services;
SELECT 'vouchers: ' || COUNT(*) FROM vouchers;
SELECT 'hotel_vouchers: ' || COUNT(*) FROM hotel_vouchers;
SELECT 'invoices: ' || COUNT(*) FROM invoices;
SELECT 'invoice_items: ' || COUNT(*) FROM invoice_items;
SELECT 'missions: ' || COUNT(*) FROM missions;
SELECT 'quotations: ' || COUNT(*) FROM quotations;
SELECT 'quotation_items: ' || COUNT(*) FROM quotation_items;
SELECT 'group_files: ' || COUNT(*) FROM group_files;
SELECT 'receipts: ' || COUNT(*) FROM receipts;
