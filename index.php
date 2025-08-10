<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محلل CSS</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            background-color: #f5f5f5;
            color: #333;
            padding: 20px;
            direction: rtl;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        textarea {
            width: 100%;
            height: 200px;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            font-family: monospace;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }

        button:hover {
            background: #2980b9;
        }

        .results {
            margin-top: 30px;
        }

        .result-section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .result-section h2 {
            margin-bottom: 10px;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .issue {
            padding: 10px;
            margin: 5px 0;
            background: #fff;
            border-left: 4px solid #e74c3c;
            border-radius: 2px;
        }

        .no-issues {
            color: #27ae60;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>محلل ملفات CSS</h1>
        <textarea id="cssInput" placeholder="الصق كود CSS هنا..."></textarea>
        <button id="analyzeBtn">حلل الملف</button>
        
        <div class="results">
            <div class="result-section">
                <h2>التكرارات في الـ Selectors</h2>
                <div id="repeatedSelectors"></div>
            </div>
            
            <div class="result-section">
                <h2>الخصائص المكررة</h2>
                <div id="repeatedProperties"></div>
            </div>
            
            <div class="result-section">
                <h2>قواعد متطابقة</h2>
                <div id="identicalRules"></div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('analyzeBtn').addEventListener('click', analyzeCSS);

        function analyzeCSS() {
            const cssText = document.getElementById('cssInput').value;
            
            if (!cssText.trim()) {
                alert('الرجاء إدخال كود CSS لتحليله');
                return;
            }

            // تحليل CSS مباشرة في المتصفح بدون الحاجة لخادم
            const analysis = analyzeCSSInBrowser(cssText);
            displayResults(analysis);
        }

        function analyzeCSSInBrowser(cssText) {
            // إزالة التعليقات
            const withoutComments = cssText.replace(/\/\*[\s\S]*?\*\//g, '');
            
            // العثور على جميع القواعد
            const rules = withoutComments.match(/[^{]+\{([^}]+)\}/g) || [];
            
            const result = {
                selectors: {},
                declarations: {},
                duplicates: {
                    selectors: {},
                    properties: {},
                    identicalRules: {}
                }
            };

            const allDeclarations = [];
            const ruleMap = new Map();

            rules.forEach(rule => {
                const [selectorPart, declarationsPart] = rule.split('{');
                const selector = selectorPart.trim();
                const declarations = declarationsPart.replace('}', '').split(';')
                    .map(d => d.trim())
                    .filter(d => d.includes(':'));

                // عدّاد للـ selectors
                result.selectors[selector] = (result.selectors[selector] || 0) + 1;

                // تحليل الـ declarations
                declarations.forEach(decl => {
                    const [property, value] = decl.split(':').map(p => p.trim());
                    const fullDecl = `${property}: ${value}`;
                    
                    // عدّاد للـ declarations
                    result.declarations[fullDecl] = (result.declarations[fullDecl] || 0) + 1;
                    allDeclarations.push({selector, property, value});
                });

                // الكشف عن القواعد المتطابقة
                const sortedDeclarations = declarations.sort().join('; ');
                if (ruleMap.has(sortedDeclarations)) {
                    ruleMap.get(sortedDeclarations).push(selector);
                } else {
                    ruleMap.set(sortedDeclarations, [selector]);
                }
            });

            // الكشف عن التكرارات
            for (const [selector, count] of Object.entries(result.selectors)) {
                if (count > 1) {
                    result.duplicates.selectors[selector] = count;
                }
            }

            for (const [decl, count] of Object.entries(result.declarations)) {
                if (count > 1) {
                    result.duplicates.properties[decl] = count;
                }
            }

            for (const [rule, selectors] of ruleMap.entries()) {
                if (selectors.length > 1) {
                    result.duplicates.identicalRules[rule] = selectors;
                }
            }

            return result;
        }

        function displayResults(data) {
            const repeatedSelectorsDiv = document.getElementById('repeatedSelectors');
            const repeatedPropertiesDiv = document.getElementById('repeatedProperties');
            const identicalRulesDiv = document.getElementById('identicalRules');

            // عرض الـ selectors المكررة
            repeatedSelectorsDiv.innerHTML = '';
            if (Object.keys(data.duplicates.selectors).length === 0) {
                repeatedSelectorsDiv.innerHTML = '<p class="no-issues">لا توجد selectors مكررة</p>';
            } else {
                for (const [selector, count] of Object.entries(data.duplicates.selectors)) {
                    const div = document.createElement('div');
                    div.className = 'issue';
                    div.innerHTML = `<strong>${selector}</strong> - تكرر ${count} مرات`;
                    repeatedSelectorsDiv.appendChild(div);
                }
            }

            // عرض الخصائص المكررة
            repeatedPropertiesDiv.innerHTML = '';
            if (Object.keys(data.duplicates.properties).length === 0) {
                repeatedPropertiesDiv.innerHTML = '<p class="no-issues">لا توجد خصائص مكررة</p>';
            } else {
                for (const [property, count] of Object.entries(data.duplicates.properties)) {
                    const div = document.createElement('div');
                    div.className = 'issue';
                    div.innerHTML = `<code>${property}</code> - تكرر ${count} مرات`;
                    repeatedPropertiesDiv.appendChild(div);
                }
            }

            // عرض القواعد المتطابقة
            identicalRulesDiv.innerHTML = '';
            if (Object.keys(data.duplicates.identicalRules).length === 0) {
                identicalRulesDiv.innerHTML = '<p class="no-issues">لا توجد قواعد متطابقة</p>';
            } else {
                for (const [rule, selectors] of Object.entries(data.duplicates.identicalRules)) {
                    const div = document.createElement('div');
                    div.className = 'issue';
                    div.innerHTML = `
                        <p><strong>القاعدة:</strong> <code>${rule}</code></p>
                        <p><strong>تستخدم في:</strong> ${selectors.join('، ')}</p>
                    `;
                    identicalRulesDiv.appendChild(div);
                }
            }
        }
    </script>
</body>
</html>
