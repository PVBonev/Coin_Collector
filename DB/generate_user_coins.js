const fs = require('fs');
const path = require('path');

const OUTPUT_FILE = path.join(__dirname, '05_user_coins_data.sql');

const USER_IDS = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
const MIN_COINS = 20;
const MAX_COINS = 30;

const BG_COINS_END_ID = 36; //
const MAX_CATALOG_ID = 100; //

const GRADES = ['UNC', 'AU', 'XF', 'VF', 'F', 'VG', 'G'];
const STATUSES = ['collection', 'collection', 'collection', 'swap', 'sell']; 
const NOTES = ['Gift from grandpa', 'Bought online', 'Found in circulation', 'Swapped with a friend', 'Flea market find', '', '', ''];
const LOCATIONS = ['eBay', 'Local Coin Shop', 'Numimarket', 'Flea Market', 'Auction House', 'Friend', 'Inherited', 'Garage Sale'];

const randomInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
const randomItem = (arr) => arr[Math.floor(Math.random() * arr.length)];

const randomDate = (start, end) => {
    return new Date(start.getTime() + Math.random() * (end.getTime() - start.getTime()))
        .toISOString().split('T')[0]; //YYYY-MM-DD
};

const generatePrice = (grade) => {
    let base = Math.random() * 20; 
    let multiplier = 1;
   switch (grade) {
        case 'UNC': multiplier = 20; break; //more expensive
        case 'AU':  multiplier = 10; break;
        case 'XF':  multiplier = 5; break;
        case 'VF':  multiplier = 2; break;
        case 'F':   multiplier = 1.2; break;//less expensive
        default:    multiplier = 1; 
    }
    return (base * multiplier).toFixed(2);
};

const generatePurchasePrice = (currentPrice) => {
    if (parseFloat(currentPrice) === 0) return '0.00';
    
    const factor = 0.5 + Math.random() * 0.6; 
    return (currentPrice * factor).toFixed(2);
};

console.log("Generating User Coins...");
let values = [];

USER_IDS.forEach(userId => {
    let coinCount, minCatId, maxCatId;

    // User 10 and 11 are bg fans
    if (userId === 10 || userId === 11) {
        console.log(`User ${userId} (BG Fan): Generating exactly 26 coins...`);
        coinCount = 26;
        minCatId = 1;
        maxCatId = BG_COINS_END_ID; // 1 to 38 are Bulgarian coins
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
        const buyPrice = generatePurchasePrice(price); 
        const buyDate = randomDate(new Date(2015, 0, 1), new Date(2025, 0, 1)); 
        const location = randomItem(LOCATIONS);  
        const note = randomItem(NOTES);
        
        const safeNote = note.replace(/'/g, "''");

        values.push(`(${userId}, ${catalogId}, '${grade}', '${status}', ${price}, ${buyPrice}, '${buyDate}', '${location}', '${safeNote}')`);
    }
});

let sql = `-- Auto-generated user coins data\n`;
sql += `-- Generated on: ${new Date().toISOString()}\n\n`;
sql += `INSERT INTO user_coins (user_id, catalog_coin_id, grade, status, price, purchase_price, purchase_date, purchase_location, private_notes) VALUES \n`;
sql += values.join(',\n');
sql += `;\n`;

try {
    fs.writeFileSync(OUTPUT_FILE, sql);
    console.log(`Success! Generated ${values.length} coins with detailed financial data.`);
    console.log(`File saved at: ${OUTPUT_FILE}`);
} catch (e) {
    console.error("Error writing file:", e);
}