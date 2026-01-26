//This script's only purpose is to generate the 02_countries_data.sql file
//which populates the countries table with data from restcountries.com API
//Technically it isn't needed as i provide 02_countries_data.sql already filled
//but this script can be used to regenerate it in the future if needed
const fs = require("fs");

async function generateSql() {
  try {
    console.log("Fetching countries form restcountries.com...");

    const url =
      "https://restcountries.com/v3.1/all?fields=name,cca2,cca3,flags,continents,region,subregion";

    const response = await fetch(url, {
      headers: {
        "User-Agent":
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(
        `HTTP Error! Status: ${response.status}. Message: ${errorText}`,
      );
    }

    const countries = await response.json();

    if (!Array.isArray(countries)) {
      console.error("CRITICAL ERROR: API did not return an array!");
      return;
    }

    console.log(`Received ${countries.length} countries. Generating SQL...`);

    let sql = `-- Auto-generated countries data\n`;
    sql += `INSERT IGNORE INTO countries (name, iso_code_2, iso_code_3, continent, flag_image) VALUES \n`;

    const values = [];

    countries.forEach((country) => {
      if (!country.name || !country.name.common) return;

      const name = country.name.common.replace(/'/g, "''");

      const iso2 = country.cca2 || "XX";
      const iso3 = country.cca3 || "XXX";
      const flag = country.flags && country.flags.png ? country.flags.png : "";

      let continent = "Unknown";
      if (country.continents && country.continents.length > 0) {
        continent = country.continents[0];
      } else if (country.region) {
        continent = country.region;
      }

      if (continent === "Americas" || continent === "America") {
        if (country.subregion && country.subregion.includes("South")) {
          continent = "South America";
        } else {
          continent = "North America";
        }
      }

      values.push(
        `('${name}', '${iso2}', '${iso3}', '${continent}', '${flag}')`,
      );
    });

    if (values.length === 0) return;

    sql += values.join(",\n") + ";";

    fs.writeFileSync("DB/02_countries_data.sql", sql);
    console.log(`Success! File 'DB/02_countries_data.sql' generated.`);
  } catch (error) {
    console.error("FAILED:", error.message);
  }
}

generateSql();
