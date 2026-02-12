// This script generates 04_catalog_coins_data.sql
// It populates catalog_coins AND the new coin_composition table with REALISTIC DATA
const fs = require("fs");
const path = require("path");

const IMAGES_REL_PATH = "../uploads/populate_coin_types";
const IMAGES_DIR = path.join(__dirname, IMAGES_REL_PATH);
const OUTPUT_FILE = path.join(__dirname, "04_catalog_coins_data.sql");

// IDs based on 01_schema.sql seed data:
// 1:Gold, 2:Silver, 3:Platinum, 4:Palladium, 5:Copper, 6:Nickel, 
// 7:Zinc, 8:Iron, 9:Aluminum, 10:Tin, 11:Bronze, 12:Brass, 13:Steel
const MATERIAL_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13];

const randomFloat = (min, max) => (Math.random() * (max - min) + min).toFixed(2);
const randomInt = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
const randomElement = (arr) => arr[Math.floor(Math.random() * arr.length)];

function getPeriod(year) {
  if (year >= 2026) return "European Union (Euro)";
  if (year >= 1992) return "Republic of Bulgaria (1992 - 2025)";
  if (year >= 1946) return "People''s Republic (1946 - 1991)";
  if (year >= 1918) return "Tsar Boris III (1918 - 1943)";
  if (year >= 1908) return "Tsar Ferdinand I (1908 - 1917)";
  return "Principality of Bulgaria (1878 - 1907)";
}

function formatDenomination(raw) {
  let text = raw.replace("bulgaria-", "");
  text = text.replace(/-/g, " ");
  return text.replace(/\b\w/g, (l) => l.toUpperCase());
}

function generateComposition(coinId) {
    const rand = Math.random();
    let components = [];

    // 60% chance for Single Material (e.g. Gold, Silver, Copper)
    if (rand < 0.60) {
        const matId = randomElement(MATERIAL_IDS);
        components.push(`(${coinId}, ${matId}, 100.00)`);
    } 
    // 30% chance for 2 Materials (Alloy like Cu-Ni)
    else if (rand < 0.90) {
        let mat1 = randomElement(MATERIAL_IDS);
        let mat2 = randomElement(MATERIAL_IDS);
        while(mat1 === mat2) mat2 = randomElement(MATERIAL_IDS); // Ensure distinct

        // Random split: 90/10, 75/25, or 50/50
        const splitRand = Math.random();
        let p1, p2;
        if(splitRand < 0.33) { p1 = 90; p2 = 10; }
        else if (splitRand < 0.66) { p1 = 75; p2 = 25; }
        else { p1 = 50; p2 = 50; }

        components.push(`(${coinId}, ${mat1}, ${p1.toFixed(2)})`);
        components.push(`(${coinId}, ${mat2}, ${p2.toFixed(2)})`);
    } 
    // 10% chance for 3 Materials (Complex alloys)
    else {
        let mat1 = randomElement(MATERIAL_IDS);
        let mat2 = randomElement(MATERIAL_IDS);
        let mat3 = randomElement(MATERIAL_IDS);
        while(mat1 === mat2) mat2 = randomElement(MATERIAL_IDS);
        while(mat3 === mat1 || mat3 === mat2) mat3 = randomElement(MATERIAL_IDS);

        // Simple split 70/20/10
        components.push(`(${coinId}, ${mat1}, 70.00)`);
        components.push(`(${coinId}, ${mat2}, 20.00)`);
        components.push(`(${coinId}, ${mat3}, 10.00)`);
    }

    return components;
}

async function generateSql() {
  try {
    console.log(`Scanning directory: ${IMAGES_DIR}...`);
    if (!fs.existsSync(IMAGES_DIR)) throw new Error(`Directory not found: ${IMAGES_DIR}`);

    const files = fs.readdirSync(IMAGES_DIR);
    
    let coinValues = [];
    let compositionValues = [];
    let globalCoinId = 1; // Start counting from 1 (assuming clean DB)

    // Real bg coins from images
    console.log(`Processing ${files.length} images for Bulgaria...`);

    files.forEach((file) => {
      if (file.startsWith(".")) return;
      if (!file.includes("(1)")) {
        const ext = path.extname(file);
        const nameWithoutExt = path.basename(file, ext);
        const match = nameWithoutExt.match(/^bulgaria-(.+)-(\d{4})$/);

        if (match) {
          const denomRaw = match[1];
          const year = parseInt(match[2], 10);
          const denomination = formatDenomination(denomRaw);
          const title = `${denomination} (${year})`;
          const period = getPeriod(year);
          const dbPathBack = `uploads/populate_coin_types/${file}`;
          const dbPathFront = `uploads/populate_coin_types/${nameWithoutExt} (1)${ext}`;

          const weight = randomFloat(2.5, 25.0);
          const diameter = randomFloat(16.0, 38.0);
          const thickness = randomFloat(1.2, 2.8);
          const mintage = randomInt(50000, 15000000);

          coinValues.push(
            `(@bg_id, '${title}', '${denomination}', ${year}, '${period}', ${weight}, ${diameter}, ${thickness}, ${mintage}, '${dbPathFront}', '${dbPathBack}', @admin_id, 1)`
          );

          const comps = generateComposition(globalCoinId);
          compositionValues.push(...comps);
          
          globalCoinId++;
        }
      }
    });

    // Dummy coins for other coi=untries
    // This ensures most countries get ~10 coins each.
    const DUMMY_COUNT = 2000; 
    console.log(`Generating ${DUMMY_COUNT} dummy coins for other countries...`);
    
    const dummyDenoms = ['1 Cent', '5 Cents', '10 Cents', '25 Cents', '50 Cents', '1 Dollar', '2 Dollars', '5 Dollars', '10 Pesos', '100 Yen', '1 Franc', '5 Kroner'];
    
    for(let i=0; i < DUMMY_COUNT; i++) {
        const year = randomInt(1950, 2024);
        const denom = randomElement(dummyDenoms);
        const title = `Generated ${denom} #${i+1}`; // Unique-ish title
        const weight = randomFloat(2, 30);
        const diameter = randomFloat(15, 40);
        const thickness = randomFloat(1, 3);
        const mintage = randomInt(10000, 500000);

        const countrySubquery = `(SELECT id FROM countries WHERE name != 'Bulgaria' ORDER BY RAND() LIMIT 1)`;

        coinValues.push(
            `(${countrySubquery}, '${title}', '${denom}', ${year}, 'Republic Era', ${weight}, ${diameter}, ${thickness}, ${mintage}, NULL, NULL, @admin_id, 1)`
        );

        const comps = generateComposition(globalCoinId);
        compositionValues.push(...comps);

        globalCoinId++;
    }

    let sql = `-- Auto-generated catalog coins data\n`;
    sql += `-- Generated on: ${new Date().toISOString()}\n\n`;
    sql += `SET @bg_id = (SELECT id FROM countries WHERE name = 'Bulgaria' LIMIT 1);\n`;
    sql += `SET @admin_id = (SELECT id FROM users WHERE username = 'TestUser' LIMIT 1);\n\n`;

    if (coinValues.length > 0) {
      sql += `-- 1. Insert Catalog Coins (Bulgaria + ${DUMMY_COUNT} World Coins)\n`;
      sql += `INSERT INTO catalog_coins (country_id, title, denomination, year, period, weight, diameter, thickness, mintage, catalog_image_front, catalog_image_back, created_by_user_id, is_approved) VALUES \n`;
      sql += coinValues.join(",\n");
      sql += `;\n\n`;
    }

    if (compositionValues.length > 0) {
        sql += `-- 2. Insert Coin Compositions (Linked by assumed ID sequence)\n`;
        sql += `INSERT INTO coin_composition (catalog_coin_id, material_id, percentage) VALUES \n`;
        sql += compositionValues.join(",\n");
        sql += `;\n`;
    }

    fs.writeFileSync(OUTPUT_FILE, sql);
    console.log(`Success! File generated at: ${OUTPUT_FILE}`);
    console.log(`Total coins generated: ${globalCoinId - 1}`);

  } catch (error) {
    console.error("FAILED:", error.message);
  }
}

generateSql();