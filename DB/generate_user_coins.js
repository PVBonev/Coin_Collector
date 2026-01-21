//This script's only purpose is to generate the 05_user_coins_data.sql file
//which populates the user_coins table with data 
//Technically it isn't needed as i provide 05_user_coins_data.sql already filled
//but this script can be used to regenerate it in the future if needed
const fs = require('fs');
const path = require('path');

const OUTPUT_FILE = path.join(__dirname, '05_user_coins_data.sql');

const USER_IDS = [2, 3, 4, 5, 6, 7, 8, 9, 10];
const MIN_COINS = 20;
const MAX_COINS = 30;

const BG_COINS_END_ID = 38; //first 38 are bulgarian coins
const MAX_CATALOG_ID = 100; //rest are world coins

const GRADES = ['UNC', 'AU', 'XF', 'VF', 'F', 'VG', 'G'];
const STATUSES = ['collection', 'collection', 'collection', 'swap', 'sell']; //collection is most likely
const NOTES = [
    'Gift from grandpa', 'Bought online', 'Found in circulation', 
    'Swapped with a friend', 'Flea market find', '', '', ''//no note more likely
];

const randomInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
const randomItem = (arr) => arr[Math.floor(Math.random() * arr.length)];

//price generation based on grade
const generatePrice = (grade) => {
    let base = Math.random() * 10; //between 0 and 10
    let multiplier = 1;

    switch (grade) {
        case 'UNC': multiplier = 20; break; //more expensive
        case 'AU':  multiplier = 10; break;
        case 'XF':  multiplier = 5; break;
        case 'VF':  multiplier = 2; break;
        case 'F':   multiplier = 1.2; break;//less expensive
        default:    multiplier = 1; 
    }

    //some variation added
    return (base * multiplier).toFixed(2);
};

console.log("Generating User Coins...");

let values = [];

USER_IDS.forEach(userId => {
    //we want user10 to have 26 bulgarian coins as in 2026
    let coinCount, minCatId, maxCatId;

    if (userId === 10) {
        console.log(`User ${userId} (BG Only): Generating exactly 26 coins...`);
        coinCount = 26;
        minCatId = 1;
        maxCatId = BG_COINS_END_ID;
    } else {
        console.log(`User ${userId}: Generating random mix...`);
        coinCount = randomInt(MIN_COINS, MAX_COINS);
        minCatId = 1;
        maxCatId = MAX_CATALOG_ID;
    }

    const usedCatalogIds = new Set();

    for (let i = 0; i < coinCount; i++) {
        let catalogId;
        let attempts = 0;
        
        do {
            catalogId = randomInt(minCatId, maxCatId);
            attempts++;
        } while (usedCatalogIds.has(catalogId) && attempts < 50);
        
        if (attempts >= 50) continue;
        
        usedCatalogIds.add(catalogId);

        const grade = randomItem(GRADES);
        const status = randomItem(STATUSES);
        const price = generatePrice(grade);
        const note = randomItem(NOTES);
        
        values.push(`(${userId}, ${catalogId}, '${grade}', '${status}', ${price}, '${note}')`);
    }
});

let sql = `-- Auto-generated user coins data\n`;
sql += `-- Generated on: ${new Date().toISOString()}\n\n`;
sql += `INSERT INTO user_coins (user_id, catalog_coin_id, grade, status, price, private_notes) VALUES \n`;
sql += values.join(',\n');
sql += `;\n`;

try {
    fs.writeFileSync(OUTPUT_FILE, sql);
    console.log(`Success! generated ${values.length} coins total.`);
    console.log(`File saved at: ${OUTPUT_FILE}`);
} catch (e) {
    console.error("Error writing file:", e);
}