<?php

declare(strict_types=1);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}
if (!function_exists('db')) {
    require BASE_PATH . '/includes/bootstrap.php';
}

$pdo = db();

function seed_insert(PDO $pdo, string $sql, array $rows): void
{
    $stmt = $pdo->prepare($sql);
    foreach ($rows as $row) {
        $stmt->execute($row);
    }
}

$pdo->exec("INSERT IGNORE INTO admin_roles (id, name, slug, permissions, created_at) VALUES
(1, 'Super Administrator', 'super-admin', '[\"*\"]', NOW()),
(2, 'Application Reviewer', 'reviewer', '[\"applicants.view\",\"applicants.review\",\"applicants.notes\"]', NOW()),
(3, 'Finance Administrator', 'finance', '[\"applicants.view\",\"grants.approve\",\"funding.manage\",\"wallet.adjust\"]', NOW()),
(4, 'Card Operations Administrator', 'card-ops', '[\"applicants.view\",\"cards.manage\",\"centres.manage\"]', NOW())");

$adminEmail = trim((string) (getenv('NARFCARD_ADMIN_EMAIL') ?: ($_ENV['NARFCARD_ADMIN_EMAIL'] ?? '')));
$adminPass = (string) (getenv('NARFCARD_ADMIN_PASSWORD') ?: ($_ENV['NARFCARD_ADMIN_PASSWORD'] ?? ''));
$adminFirst = trim((string) (getenv('NARFCARD_ADMIN_FIRST') ?: ($_ENV['NARFCARD_ADMIN_FIRST'] ?? 'Programme')));
$adminLast = trim((string) (getenv('NARFCARD_ADMIN_LAST') ?: ($_ENV['NARFCARD_ADMIN_LAST'] ?? 'Administrator')));

if ($adminEmail !== '' && $adminPass !== '') {
    $stmt = $pdo->prepare('SELECT id FROM administrators WHERE email = ?');
    $stmt->execute([$adminEmail]);
    if (!$stmt->fetch()) {
        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO administrators (role_id, first_name, last_name, email, password_hash, is_active, created_at, updated_at) VALUES (1, ?, ?, ?, ?, 1, NOW(), NOW())')
            ->execute([$adminFirst !== '' ? $adminFirst : 'Programme', $adminLast !== '' ? $adminLast : 'Administrator', $adminEmail, $hash]);
    }
} elseif (PHP_SAPI === 'cli') {
    echo "Skipped Super Administrator (set NARFCARD_ADMIN_EMAIL and NARFCARD_ADMIN_PASSWORD, or use install.php).\n";
}

ensure_settings_rows();

$pdo->exec("INSERT INTO grant_funding (source_name, total_amount, allocated_amount, available_amount, notes, created_at, updated_at)
SELECT 'NARFCARD Programme Relief Pool', 50000000, 0, 50000000, 'Development seed pool. Not a government appropriation.', NOW(), NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM grant_funding LIMIT 1)");

$states = [
    ['Abia', 'AB'], ['Adamawa', 'AD'], ['Akwa Ibom', 'AK'], ['Anambra', 'AN'], ['Bauchi', 'BA'],
    ['Bayelsa', 'BY'], ['Benue', 'BE'], ['Borno', 'BO'], ['Cross River', 'CR'], ['Delta', 'DE'],
    ['Ebonyi', 'EB'], ['Edo', 'ED'], ['Ekiti', 'EK'], ['Enugu', 'EN'], ['FCT', 'FC'],
    ['Gombe', 'GO'], ['Imo', 'IM'], ['Jigawa', 'JI'], ['Kaduna', 'KD'], ['Kano', 'KN'],
    ['Katsina', 'KT'], ['Kebbi', 'KE'], ['Kogi', 'KO'], ['Kwara', 'KW'], ['Lagos', 'LA'],
    ['Nasarawa', 'NA'], ['Niger', 'NI'], ['Ogun', 'OG'], ['Ondo', 'ON'], ['Osun', 'OS'],
    ['Oyo', 'OY'], ['Plateau', 'PL'], ['Rivers', 'RI'], ['Sokoto', 'SO'], ['Taraba', 'TA'],
    ['Yobe', 'YO'], ['Zamfara', 'ZA'],
];
$st = $pdo->prepare('INSERT IGNORE INTO states (name, code) VALUES (?, ?)');
foreach ($states as $row) {
    $st->execute($row);
}

$lgas = [
    'Abia' => ['Aba North','Aba South','Arochukwu','Bende','Ikwuano','Isiala Ngwa North','Isiala Ngwa South','Isuikwuato','Obi Ngwa','Ohafia','Osisioma','Ugwunagbo','Ukwa East','Ukwa West','Umuahia North','Umuahia South','Umu Nneochi'],
    'Adamawa' => ['Demsa','Fufore','Ganye','Girei','Gombi','Guyuk','Hong','Jada','Lamurde','Madagali','Maiha','Mayo-Belwa','Michika','Mubi North','Mubi South','Numan','Shelleng','Song','Toungo','Yola North','Yola South'],
    'Akwa Ibom' => ['Abak','Eastern Obolo','Eket','Esit Eket','Essien Udim','Etim Ekpo','Etinan','Ibeno','Ibesikpo Asutan','Ibiono Ibom','Ika','Ikono','Ikot Abasi','Ikot Ekpene','Ini','Itu','Mbo','Mkpat Enin','Nsit Atai','Nsit Ibom','Nsit Ubium','Obot Akara','Okobo','Onna','Oron','Oruk Anam','Udung Uko','Ukanafun','Uruan','Urue Offong/Oruko','Uyo'],
    'Anambra' => ['Aguata','Anambra East','Anambra West','Anaocha','Awka North','Awka South','Ayamelum','Dunukofia','Ekwusigo','Idemili North','Idemili South','Ihiala','Njikoka','Nnewi North','Nnewi South','Ogbaru','Onitsha North','Onitsha South','Orumba North','Orumba South','Oyi'],
    'Bauchi' => ['Alkaleri','Bauchi','Bogoro','Damban','Darazo','Dass','Gamawa','Ganjuwa','Giade','Itas/Gadau','Jama\'are','Katagum','Kirfi','Misau','Ningi','Shira','Tafawa Balewa','Toro','Warji','Zaki'],
    'Bayelsa' => ['Brass','Ekeremor','Kolokuma/Opokuma','Nembe','Ogbia','Sagbama','Southern Ijaw','Yenagoa'],
    'Benue' => ['Ado','Agatu','Apa','Buruku','Gboko','Guma','Gwer East','Gwer West','Katsina-Ala','Konshisha','Kwande','Logo','Makurdi','Obi','Ogbadibo','Ohimini','Oju','Okpokwu','Otukpo','Tarka','Ukum','Ushongo','Vandeikya'],
    'Borno' => ['Abadam','Askira/Uba','Bama','Bayo','Biu','Chibok','Damboa','Dikwa','Gubio','Guzamala','Gwoza','Hawul','Jere','Kaga','Kala/Balge','Konduga','Kukawa','Kwaya Kusar','Mafa','Magumeri','Maiduguri','Marte','Mobbar','Monguno','Ngala','Nganzai','Shani'],
    'Cross River' => ['Abi','Akamkpa','Akpabuyo','Bakassi','Bekwarra','Biase','Boki','Calabar Municipal','Calabar South','Etung','Ikom','Obanliku','Obubra','Obudu','Odukpani','Ogoja','Yakurr','Yala'],
    'Delta' => ['Aniocha North','Aniocha South','Bomadi','Burutu','Ethiope East','Ethiope West','Ika North East','Ika South','Isoko North','Isoko South','Ndokwa East','Ndokwa West','Okpe','Oshimili North','Oshimili South','Patani','Sapele','Udu','Ughelli North','Ughelli South','Ukwuani','Uvwie','Warri North','Warri South','Warri South West'],
    'Ebonyi' => ['Abakaliki','Afikpo North','Afikpo South','Ebonyi','Ezza North','Ezza South','Ikwo','Ishielu','Ivo','Izzi','Ohaozara','Ohaukwu','Onicha'],
    'Edo' => ['Akoko-Edo','Egor','Esan Central','Esan North-East','Esan South-East','Esan West','Etsako Central','Etsako East','Etsako West','Igueben','Ikpoba-Okha','Oredo','Orhionmwon','Ovia North-East','Ovia South-West','Owan East','Owan West','Uhunmwonde'],
    'Ekiti' => ['Ado Ekiti','Efon','Ekiti East','Ekiti South-West','Ekiti West','Emure','Gbonyin','Ido Osi','Ijero','Ikere','Ikole','Ilejemeje','Irepodun/Ifelodun','Ise/Orun','Moba','Oye'],
    'Enugu' => ['Aninri','Awgu','Enugu East','Enugu North','Enugu South','Ezeagu','Igbo Etiti','Igbo Eze North','Igbo Eze South','Isi Uzo','Nkanu East','Nkanu West','Nsukka','Oji River','Udenu','Udi','Uzo-Uwani'],
    'FCT' => ['Abaji','Bwari','Gwagwalada','Kuje','Kwali','Municipal Area Council'],
    'Gombe' => ['Akko','Balanga','Billiri','Dukku','Funakaye','Gombe','Kaltungo','Kwami','Nafada','Shongom','Yamaltu/Deba'],
    'Imo' => ['Aboh Mbaise','Ahiazu Mbaise','Ehime Mbano','Ezinihitte','Ideato North','Ideato South','Ihitte/Uboma','Ikeduru','Isiala Mbano','Isu','Mbaitoli','Ngor Okpala','Njaba','Nkwerre','Nwangele','Obowo','Oguta','Ohaji/Egbema','Okigwe','Onuimo','Orlu','Orsu','Oru East','Oru West','Owerri Municipal','Owerri North','Owerri West'],
    'Jigawa' => ['Auyo','Babura','Biriniwa','Birnin Kudu','Buji','Dutse','Gagarawa','Garki','Gumel','Guri','Gwaram','Gwiwa','Hadejia','Jahun','Kafin Hausa','Kaugama','Kazaure','Kiri Kasama','Kiyawa','Maigatari','Malam Madori','Miga','Ringim','Roni','Sule Tankarkar','Taura','Yankwashi'],
    'Kaduna' => ['Birnin Gwari','Chikun','Giwa','Igabi','Ikara','Jaba','Jema\'a','Kachia','Kaduna North','Kaduna South','Kagarko','Kajuru','Kaura','Kauru','Kubau','Kudan','Lere','Makarfi','Sabon Gari','Sanga','Soba','Zangon Kataf','Zaria'],
    'Kano' => ['Ajingi','Albasu','Bagwai','Bebeji','Bichi','Bunkure','Dala','Dambatta','Dawakin Kudu','Dawakin Tofa','Doguwa','Fagge','Gabasawa','Garko','Garun Mallam','Gaya','Gezawa','Gwale','Gwarzo','Kabo','Kano Municipal','Karaye','Kibiya','Kiru','Kumbotso','Kunchi','Kura','Madobi','Makoda','Minjibir','Nasarawa','Rano','Rimin Gado','Rogo','Shanono','Sumaila','Takai','Tarauni','Tofa','Tsanyawa','Tudun Wada','Ungogo','Warawa','Wudil'],
    'Katsina' => ['Bakori','Batagarawa','Batsari','Baure','Bindawa','Charanchi','Dandume','Danja','Dan Musa','Daura','Dutsi','Dutsin-Ma','Faskari','Funtua','Ingawa','Jibia','Kafur','Kaita','Kankara','Kankia','Katsina','Kurfi','Kusada','Mai\'Adua','Malumfashi','Mani','Mashi','Matazu','Musawa','Rimi','Sabuwa','Safana','Sandamu','Zango'],
    'Kebbi' => ['Aleiro','Arewa Dandi','Argungu','Augie','Bagudo','Birnin Kebbi','Bunza','Dandi','Fakai','Gwandu','Jega','Kalgo','Koko/Besse','Maiyama','Ngaski','Sakaba','Shanga','Suru','Wasagu/Danko','Yauri','Zuru'],
    'Kogi' => ['Adavi','Ajaokuta','Ankpa','Bassa','Dekina','Ibaji','Idah','Igalamela-Odolu','Ijumu','Kabba/Bunu','Kogi','Lokoja','Mopa-Muro','Ofu','Ogori/Magongo','Okehi','Okene','Olamaboro','Omala','Yagba East','Yagba West'],
    'Kwara' => ['Asa','Baruten','Edu','Ekiti','Ifelodun','Ilorin East','Ilorin South','Ilorin West','Irepodun','Isin','Kaiama','Moro','Offa','Oke Ero','Oyun','Pategi'],
    'Lagos' => ['Agege','Ajeromi-Ifelodun','Alimosho','Amuwo-Odofin','Apapa','Badagry','Epe','Eti-Osa','Ibeju-Lekki','Ifako-Ijaiye','Ikeja','Ikorodu','Kosofe','Lagos Island','Lagos Mainland','Mushin','Ojo','Oshodi-Isolo','Shomolu','Surulere'],
    'Nasarawa' => ['Akwanga','Awe','Doma','Karu','Keana','Keffi','Kokona','Lafia','Nasarawa','Nasarawa Egon','Obi','Toto','Wamba'],
    'Niger' => ['Agaie','Agwara','Bida','Borgu','Bosso','Chanchaga','Edati','Gbako','Gurara','Katcha','Kontagora','Lapai','Lavun','Magama','Mariga','Mashegu','Mokwa','Moya','Paikoro','Rafi','Rijau','Shiroro','Suleja','Tafa','Wushishi'],
    'Ogun' => ['Abeokuta North','Abeokuta South','Ado-Odo/Ota','Ewekoro','Ifo','Ijebu East','Ijebu North','Ijebu North East','Ijebu Ode','Ikenne','Imeko Afon','Ipokia','Obafemi Owode','Odeda','Odogbolu','Ogun Waterside','Remo North','Shagamu','Yewa North','Yewa South'],
    'Ondo' => ['Akoko North-East','Akoko North-West','Akoko South-East','Akoko South-West','Akure North','Akure South','Ese Odo','Idanre','Ifedore','Ilaje','Ile Oluji/Okeigbo','Irele','Odigbo','Okitipupa','Ondo East','Ondo West','Ose','Owo'],
    'Osun' => ['Atakunmosa East','Atakunmosa West','Aiyedaade','Aiyedire','Boluwaduro','Boripe','Ede North','Ede South','Egbedore','Ejigbo','Ife Central','Ife East','Ife North','Ife South','Ifedayo','Ifelodun','Ila','Ilesa East','Ilesa West','Irepodun','Irewole','Isokan','Iwo','Obokun','Odo Otin','Ola Oluwa','Olorunda','Oriade','Orolu','Osogbo'],
    'Oyo' => ['Afijio','Akinyele','Atiba','Atisbo','Egbeda','Ibadan North','Ibadan North-East','Ibadan North-West','Ibadan South-East','Ibadan South-West','Ibarapa Central','Ibarapa East','Ibarapa North','Ido','Irepo','Iseyin','Itesiwaju','Iwajowa','Kajola','Lagelu','Ogbomosho North','Ogbomosho South','Ogo Oluwa','Olorunsogo','Oluyole','Ona Ara','Orelope','Ori Ire','Oyo East','Oyo West','Saki East','Saki West','Surulere'],
    'Plateau' => ['Barkin Ladi','Bassa','Bokkos','Jos East','Jos North','Jos South','Kanam','Kanke','Langtang North','Langtang South','Mangu','Mikang','Pankshin','Qua\'an Pan','Riyom','Shendam','Wase'],
    'Rivers' => ['Abua/Odual','Ahoada East','Ahoada West','Akuku Toru','Andoni','Asari-Toru','Bonny','Degema','Eleme','Emuoha','Etche','Gokana','Ikwerre','Khana','Obio/Akpor','Ogba/Egbema/Ndoni','Ogu/Bolo','Okrika','Omuma','Opobo/Nkoro','Oyigbo','Port Harcourt','Tai'],
    'Sokoto' => ['Binji','Bodinga','Dange Shuni','Gada','Goronyo','Gudu','Gwadabawa','Illela','Isa','Kebbe','Kware','Rabah','Sabon Birni','Shagari','Silame','Sokoto North','Sokoto South','Tambuwal','Tangaza','Tureta','Wamako','Wurno','Yabo'],
    'Taraba' => ['Ardo Kola','Bali','Donga','Gashaka','Gassol','Ibi','Jalingo','Karim Lamido','Kurmi','Lau','Sardauna','Takum','Ussa','Wukari','Yorro','Zing'],
    'Yobe' => ['Bade','Bursari','Damaturu','Fika','Fune','Geidam','Gujba','Gulani','Jakusko','Karasuwa','Machina','Nangere','Nguru','Potiskum','Tarmuwa','Yunusari','Yusufari'],
    'Zamfara' => ['Anka','Bakura','Birnin Magaji','Bukkuyum','Bungudu','Gummi','Gusau','Kaura Namoda','Maradun','Maru','Shinkafi','Talata Mafara','Tsafe','Zurmi'],
];

$stateIds = [];
foreach ($pdo->query('SELECT id, name FROM states') as $row) {
    $stateIds[$row['name']] = (int) $row['id'];
}
$lgaStmt = $pdo->prepare('INSERT IGNORE INTO local_governments (state_id, name) VALUES (?, ?)');
$countLga = (int) $pdo->query('SELECT COUNT(*) FROM local_governments')->fetchColumn();
if ($countLga === 0) {
    foreach ($lgas as $stateName => $list) {
        $sid = $stateIds[$stateName] ?? null;
        if (!$sid) {
            continue;
        }
        foreach ($list as $lga) {
            $lgaStmt->execute([$sid, $lga]);
        }
    }
}

$banks = [
    ['Access Bank', '044'], ['Citibank Nigeria', '023'], ['Ecobank Nigeria', '050'],
    ['Fidelity Bank', '070'], ['First Bank of Nigeria', '011'], ['First City Monument Bank', '214'],
    ['Globus Bank', '00103'], ['Guaranty Trust Bank', '058'], ['Heritage Bank', '030'],
    ['Jaiz Bank', '301'], ['Keystone Bank', '082'], ['Kuda Bank', '090267'],
    ['Polaris Bank', '076'], ['Providus Bank', '101'], ['Stanbic IBTC Bank', '221'],
    ['Standard Chartered Bank', '068'], ['Sterling Bank', '232'], ['SunTrust Bank', '100'],
    ['Union Bank of Nigeria', '032'], ['United Bank for Africa', '033'], ['Unity Bank', '215'],
    ['Wema Bank', '035'], ['Zenith Bank', '057'], ['Opay', '100004'], ['PalmPay', '100033'],
];
$b = $pdo->prepare('INSERT IGNORE INTO banks (name, code, is_active) VALUES (?, ?, 1)');
foreach ($banks as $row) {
    $b->execute($row);
}

$occupations = [
    ['Farmer', 'Agriculture'], ['Trader', 'Commerce'], ['Artisan', 'Skilled Trade'],
    ['Civil Servant', 'Public Service'], ['Teacher', 'Education'], ['Healthcare Worker', 'Health'],
    ['Driver', 'Transport'], ['Market Vendor', 'Commerce'], ['Tailor', 'Skilled Trade'],
    ['Hairdresser/Barber', 'Services'], ['Security Personnel', 'Services'], ['Student', 'Education'],
    ['Unemployed', 'None'], ['Retired', 'None'], ['Self-employed', 'Business'],
    ['Construction Worker', 'Labour'], ['Domestic Worker', 'Services'], ['Fisherman', 'Agriculture'],
    ['Okada/Keke Rider', 'Transport'], ['Other', 'Other'],
];
$o = $pdo->prepare('INSERT IGNORE INTO occupations (name, category) VALUES (?, ?)');
foreach ($occupations as $row) {
    $o->execute($row);
}

$centres = [
    ['Lagos Island Collection Centre', 'Lagos', 'Lagos Island', 'TBS Complex, Tafawa Balewa Square, Lagos Island', '+234 1 700 2273'],
    ['Ikeja Collection Centre', 'Lagos', 'Ikeja', 'State Secretariat Complex, Alausa, Ikeja', '+234 1 700 2274'],
    ['Abuja Central Collection Centre', 'FCT', 'Municipal Area Council', 'Area 11, Garki, Abuja', '+234 9 700 2273'],
    ['Port Harcourt Collection Centre', 'Rivers', 'Port Harcourt', 'Aba Road, Port Harcourt', '+234 84 700 2273'],
    ['Kano Collection Centre', 'Kano', 'Kano Municipal', 'Zoo Road, Kano', '+234 64 700 2273'],
    ['Ibadan Collection Centre', 'Oyo', 'Ibadan North', 'Secretariat, Agodi, Ibadan', '+234 2 700 2273'],
    ['Enugu Collection Centre', 'Enugu', 'Enugu North', 'Independence Layout, Enugu', '+234 42 700 2273'],
    ['Kaduna Collection Centre', 'Kaduna', 'Kaduna North', 'Yakubu Gowon Way, Kaduna', '+234 62 700 2273'],
];
if ((int) $pdo->query('SELECT COUNT(*) FROM collection_centres')->fetchColumn() === 0) {
    $c = $pdo->prepare('INSERT INTO collection_centres (name, state_id, lga_id, address, phone, is_active) VALUES (?, ?, ?, ?, ?, 1)');
    foreach ($centres as $row) {
        $sid = $stateIds[$row[1]] ?? null;
        $lgaId = null;
        if ($sid) {
            $ls = $pdo->prepare('SELECT id FROM local_governments WHERE state_id = ? AND name = ? LIMIT 1');
            $ls->execute([$sid, $row[2]]);
            $lgaId = $ls->fetchColumn() ?: null;
        }
        if ($sid) {
            $c->execute([$row[0], $sid, $lgaId, $row[3], $row[4]]);
        }
    }
}

if (PHP_SAPI === 'cli') {
    echo "Seed completed.\n";
}
