//This script's only purpose is to generate the 04_catalog_coins_data.sql file
//which populates the catalog_coins table with data
//Technically it isn't needed as i provide 04_catalog_coins_data.sql already filled
//but this script can be used to regenerate it in the future if needed
const fs = require("fs");
const path = require("path");

const IMAGES_REL_PATH = "../uploads/populate_coin_types";
const IMAGES_DIR = path.join(__dirname, IMAGES_REL_PATH);
const OUTPUT_FILE = path.join(__dirname, "04_catalog_coins_data.sql");

const MATERIALS = [
  "Copper-Nickel",
  "Bronze",
  "Silver",
  "Steel",
  "Aluminium",
  "Gold",
  "Zinc",
  "Copper",
  "Brass",
  "Bi-Metallic",
];

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

const randomFloat = (min, max) =>
  (Math.random() * (max - min) + min).toFixed(2);
const randomInt = (min, max) =>
  Math.floor(Math.random() * (max - min + 1)) + min;
const randomItem = (arr) => arr[Math.floor(Math.random() * arr.length)];

async function generateSql() {
  try {
    console.log(`Scanning directory: ${IMAGES_DIR}...`);

    if (!fs.existsSync(IMAGES_DIR)) {
      throw new Error(`Directory not found: ${IMAGES_DIR}`);
    }

    const files = fs.readdirSync(IMAGES_DIR);
    const values = [];

    console.log(`Found ${files.length} files. Parsing filenames...`);

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

          const material = randomItem(MATERIALS);
          const weight = randomFloat(2.5, 25.0);
          const diameter = randomFloat(16.0, 38.0);
          const thickness = randomFloat(1.2, 2.8);
          const mintage = randomInt(50000, 15000000);

          values.push(
            `(@bg_id, '${title}', '${denomination}', ${year}, '${period}', '${material}', ${weight}, ${diameter}, ${thickness}, ${mintage}, '${dbPathFront}', '${dbPathBack}', @admin_id, 1)`,
          );
        }
      }
    });

    let sql = `-- Auto-generated catalog coins data\n`;
    sql += `-- Generated on: ${new Date().toISOString()}\n\n`;

    sql += `SET @bg_id = (SELECT id FROM countries WHERE name = 'Bulgaria' LIMIT 1);\n`;
    sql += `SET @admin_id = (SELECT id FROM users WHERE username = 'TestUser' LIMIT 1);\n\n`;

    if (values.length > 0) {
      sql += `INSERT INTO catalog_coins (country_id, title, denomination, year, period, material, weight, diameter, thickness, mintage, catalog_image_front, catalog_image_back, created_by_user_id, is_approved) VALUES \n`;
      sql += values.join(",\n");
      sql += `;\n\n`;
    }

    sql += `INSERT INTO catalog_coins (country_id, title, denomination, year, period, material, weight, diameter, thickness, mintage, description, created_by_user_id, is_approved)\n`;
    sql += `SELECT \n`;
    sql += `    c.id,\n`;
    sql += `    CONCAT(c.name, ' Coin #', numbers.n), \n`;
    sql += `    ELT(FLOOR(1 + (RAND() * 6)), '1 Cent', '5 Cents', '10 Cents', '25 Cents', '1 Dollar', '5 Dollars'),\n`;
    sql += `    FLOOR(1950 + (RAND() * 70)), \n`;
    sql += `    'Republic Era',\n`;
    sql += `    ELT(FLOOR(1 + (RAND() * 6)), 'Copper', 'Nickel', 'Steel', 'Silver', 'Gold', 'Bronze'),\n`;
    sql += `    ROUND(2 + (RAND() * 28), 2),  -- Weight\n`;
    sql += `    ROUND(15 + (RAND() * 25), 2), -- Diameter\n`;
    sql += `    ROUND(1 + (RAND() * 2), 2),   -- Thickness\n`;
    sql += `    FLOOR(10000 + (RAND() * 10000000)), -- Mintage\n`;

    sql += `    'Auto-generated coin for demo.',\n`;
    sql += `    @admin_id,\n`;
    sql += `    1\n`;
    sql += `FROM countries c\n`;
    sql += `CROSS JOIN (\n`;
    sql += `    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 \n`;
    sql += `    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 \n`;
    sql += `    UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 \n`;
    sql += `) AS numbers\n`;
    sql += `WHERE c.name != 'Bulgaria'\n`;
    sql += `  AND numbers.n <= FLOOR(5 + (RAND(c.id * 333) * 11)) \n`;
    sql += `ORDER BY c.name, numbers.n;\n`;

    fs.writeFileSync(OUTPUT_FILE, sql);
    console.log(`Success! File generated at: ${OUTPUT_FILE}`);
  } catch (error) {
    console.error("FAILED:", error.message);
  }
}

generateSql();
