<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Coin Grading Guide - Sheldon Scale</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .guide-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .grade-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .grade-item:last-child { border-bottom: none; }
        
        .grade-code {
            font-weight: bold;
            color: var(--accent-color);
            font-size: 1.1rem;
            display: inline-block;
            min-width: 80px;
        }
        .grade-title {
            font-weight: bold;
            color: #333;
            font-size: 1.1rem;
        }
        .grade-desc {
            color: #555;
            margin-top: 5px;
            line-height: 1.5;
        }
        .intro-text {
            font-size: 1.05rem;
            line-height: 1.6;
            color: #444;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h1 style="border-bottom: 3px solid var(--accent-color); display: inline-block; padding-bottom: 5px;">
            The Sheldon Grading Scale
        </h1>

        <div class="guide-section">
            <p class="intro-text">
                The Sheldon Grading Scale is a 70-point scale for grading coins, developed by Dr. William Sheldon in 1949. 
                It is the standard for grading U.S. coins and is used by major third-party grading services when assigning a grade to a coin.
            </p>
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
                <div style="flex: 1; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <strong>Business Strike</strong><br>
                    Coin struck for the purpose of becoming circulating coinage and as such intended for commerce, rather than for collectors.
                </div>
                <div style="flex: 1; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                    <strong>Proof</strong><br>
                    Typically designed for collectors and not commerce. A specially made coin distinguished by sharpness of detail & usually with a brilliant, mirror-like surface. Proof refers to the method of manufacture and is not a grade.
                </div>
            </div>
        </div>

        <h2 style="margin-top: 30px;">Mint State (Uncirculated)</h2>
        <div class="guide-section">
            <p style="margin-bottom: 20px; font-style: italic;">
                The terms Mint State (MS) and Uncirculated (Unc.) are interchangeable and refer to coins showing no trace of wear.
            </p>

            <div class="grade-item">
                <span class="grade-code">MS-70</span> <span class="grade-title">Perfect Uncirculated</span>
                <div class="grade-desc">
                    Perfect new condition, showing no trace of wear. The finest quality possible, with no evidence of scratches, handling, or contact with other coins. Very few circulation-issue coins are ever found in this condition.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">MS-65</span> <span class="grade-title">Gem Uncirculated</span>
                <div class="grade-desc">
                    An above-average uncirculated coin that may be brilliant or lightly toned and that has very few contact marks on the surface or rim.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">MS-63</span> <span class="grade-title">Choice Uncirculated</span>
                <div class="grade-desc">
                    A coin with some distracting contact marks or blemishes in prime focal areas. Luster may be impaired.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">MS-60</span> <span class="grade-title">Uncirculated</span>
                <div class="grade-desc">
                    A coin that has no trace of wear, but which may show a number of contact marks, and whose surface may be spotted or lack some luster.
                </div>
            </div>
        </div>

        <h2>Circulated Grades</h2>
        <div class="guide-section">
            
            <div class="grade-item">
                <span class="grade-code">AU-55</span> <span class="grade-title">Choice About Uncirculated</span>
                <div class="grade-desc">
                    Evidence of friction on high points of design. Most of the mint luster remains.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">AU-50</span> <span class="grade-title">About Uncirculated</span>
                <div class="grade-desc">
                    Traces of light wear on many of the high points. At least half of the mint luster is still present.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">EF-45</span> <span class="grade-title">Choice Extremely Fine</span>
                <div class="grade-desc">
                    Light overall wear on the highest points. All design details are very sharp. Some of the mint luster may show.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">EF-40</span> <span class="grade-title">Extremely Fine</span>
                <div class="grade-desc">
                    Light wear on the design throughout, but all features are sharp and well defined. Traces of luster may show.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">VF-30</span> <span class="grade-title">Choice Very Fine</span>
                <div class="grade-desc">
                    Light, even wear on the surface and highest parts of the design. All lettering and major features are sharp.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">VF-20</span> <span class="grade-title">Very Fine</span>
                <div class="grade-desc">
                    Moderate wear on high points of the design. All major details are clear.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">F-12</span> <span class="grade-title">Fine</span>
                <div class="grade-desc">
                    Moderate to considerable even wear. The entire design is bold with an overall pleasing appearance.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">VG-8</span> <span class="grade-title">Very Good</span>
                <div class="grade-desc">
                    Well-worn with main features clear and bold, although rather flat.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">G-4</span> <span class="grade-title">Good</span>
                <div class="grade-desc">
                    Heavily worn, with the design visible but faint in areas. Many details are flat.
                </div>
            </div>

            <div class="grade-item">
                <span class="grade-code">AG-3</span> <span class="grade-title">About Good</span>
                <div class="grade-desc">
                    Very heavily worn with portions of the lettering, date, and legend worn smooth. The date may be barely readable.
                </div>
            </div>

        </div>

        <div style="text-align: center; margin-bottom: 40px;">
            <a href="index.php" class="btn btn-accent">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>