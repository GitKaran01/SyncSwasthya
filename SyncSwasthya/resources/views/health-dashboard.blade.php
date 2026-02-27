<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SyncSwasthya – Health Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body{
            background:#f5f7fb;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .navbar{
            background:#ffffff;
            border-bottom:1px solid #e1e4ed;
        }
        .card{
            border-radius:12px;
            border:1px solid #e1e4ed;
            box-shadow:0 8px 18px rgba(15,23,42,.06);
        }
        .stat-label{
            font-size:12px;
            text-transform:uppercase;
            color:#6b7280;
            font-weight:600;
        }
        .stat-value{
            font-size:28px;
            font-weight:700;
            color:#111827;
        }
        .scroll-table{
            max-height:380px;
            overflow-y:auto;
        }
        .pill{
            background:#e5ecff;
            padding:4px 10px;
            border-radius:999px;
            font-size:11px;
            color:#1f2937;
        }
        .tag{
            background:#e5ecff;
            padding:2px 6px;
            border-radius:8px;
            font-size:11px;
            margin-right:3px;
        }
    </style>
</head>
<body>

<nav class="navbar px-4 py-2">
    <div class="d-flex align-items-center justify-content-between w-100">
        <h5 class="m-0 fw-bold">SyncSwasthya – Health Survey Dashboard</h5>
        <span class="pill">Last updated: <span id="last-updated">–</span></span>
    </div>
</nav>

<div class="container-fluid py-4 px-4">

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <div class="stat-label">Total Families</div>
                <div class="stat-value" id="stat-families">–</div>
                <small id="stat-villages" class="text-muted"></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="stat-label">Total Members</div>
                <div class="stat-value" id="stat-members">–</div>
                <small class="text-muted">Includes family heads + all members</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="stat-label">Avg Sleep (hrs)</div>
                <div class="stat-value" id="stat-sleep">–</div>
                <small id="stat-sleep-count" class="text-muted"></small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="stat-label">Avg BMI (Head)</div>
                <div class="stat-value" id="stat-bmi">–</div>
                <small id="stat-bmi-count" class="text-muted"></small>
            </div>
        </div>
    </div>

    <!-- TOP ROW: TABLE + AGE CHART -->
    <div class="row g-3 mb-4">
        <!-- Families table -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Families & Members</span>
                    <span class="pill" id="table-info">0 records</span>
                </div>
                <div class="scroll-table">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Head</th>
                            <th>Village</th>
                            <th>Members</th>
                            <th>Head Age</th>
                            <th>Head BMI</th>
                            <th>Alcohol</th>
                        </tr>
                        </thead>
                        <tbody id="families-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Age distribution -->
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header fw-semibold">
                    Age Distribution (Heads + Members)
                </div>
                <div class="card-body">
                    <canvas id="ageChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- SECOND ROW: LIFESTYLE CHARTS -->
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">
                    Alcohol Consumption (Heads)
                </div>
                <div class="card-body">
                    <canvas id="alcoholChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header fw-semibold">
                    Sleep Hours (Heads)
                </div>
                <div class="card-body">
                    <canvas id="sleepChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const API_URL = "{{ url('/api/v1/families') }}";

    let ageChartInstance = null;
    let alcoholChartInstance = null;
    let sleepChartInstance = null;

    async function loadData() {
        try {
            const res = await fetch(API_URL, {
                headers: { 'Accept': 'application/json' }
            });
            const families = await res.json();
            document.getElementById('last-updated').textContent = new Date().toLocaleTimeString();

            renderDashboard(families);
        } catch (e) {
            console.error('Error fetching data', e);
            alert('Failed to load data from API');
        }
    }

    function renderDashboard(families) {
        // ===== BASIC COUNTS =====
        const totalFamilies = families.length;
        const totalMembersOnly = families.reduce((sum, f) => sum + (f.members ? f.members.length : 0), 0);
        const totalPeople = totalFamilies + totalMembersOnly;

        document.getElementById('stat-families').textContent = totalFamilies;
        document.getElementById('stat-members').textContent = totalPeople;

        // unique villages
        const villages = [...new Set(families.map(f => f.village))];
        document.getElementById('stat-villages').textContent = villages.length + ' villages';

        // ===== TABLE =====
        const tbody = document.getElementById('families-body');
        tbody.innerHTML = '';
        families.forEach(f => {
            const demo = f.demographics || {};
            const life = f.lifestyle || {};
            const vitals = f.vitals || {};
            const addictions = f.addictions || {};

            const age = demo.age ?? '–';
            const bmi = vitals.bmi ?? '–';
            const alcohol = addictions.alcohol ?? '–';
            const memberCount = f.members ? f.members.length : 0;

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${f.id}</td>
                <td>${f.head_name}</td>
                <td>${f.village}</td>
                <td>${memberCount}</td>
                <td>${age}</td>
                <td>${bmi}</td>
                <td>${alcohol}</td>
            `;
            tbody.appendChild(tr);
        });
        document.getElementById('table-info').textContent = `${families.length} families, ${totalMembersOnly} members`;

        // ===== AVERAGE SLEEP (heads only) =====
        let sleepValues = [];
        families.forEach(f => {
            const lifestyle = f.lifestyle || {};
            const sh = lifestyle.sleep_hours;
            if (typeof sh === 'string') {
                const match = sh.match(/(\d+)\s*-\s*(\d+)/);
                if (match) {
                    const avg = (parseInt(match[1]) + parseInt(match[2])) / 2;
                    sleepValues.push(avg);
                } else {
                    const single = parseFloat(sh);
                    if (!isNaN(single)) sleepValues.push(single);
                }
            }
        });

        if (sleepValues.length) {
            const avgSleep = (sleepValues.reduce((a, b) => a + b, 0) / sleepValues.length).toFixed(1);
            document.getElementById('stat-sleep').textContent = avgSleep;
            document.getElementById('stat-sleep-count').textContent = `${sleepValues.length} head records`;
        } else {
            document.getElementById('stat-sleep').textContent = '–';
            document.getElementById('stat-sleep-count').textContent = 'No sleep data';
        }

        // ===== AVERAGE BMI (heads) =====
        let bmiValues = [];
        families.forEach(f => {
            const vitals = f.vitals || {};
            const bmi = parseFloat(vitals.bmi);
            if (!isNaN(bmi)) bmiValues.push(bmi);
        });

        if (bmiValues.length) {
            const avgBMI = (bmiValues.reduce((a, b) => a + b, 0) / bmiValues.length).toFixed(1);
            document.getElementById('stat-bmi').textContent = avgBMI;
            document.getElementById('stat-bmi-count').textContent = `${bmiValues.length} head records`;
        } else {
            document.getElementById('stat-bmi').textContent = '–';
            document.getElementById('stat-bmi-count').textContent = 'No BMI data';
        }

        // ===== AGE DISTRIBUTION (heads + members) =====
        const ageBuckets = {
            '0–14': 0,
            '15–24': 0,
            '25–44': 0,
            '45–59': 0,
            '60+': 0
        };

        function addAge(ageVal) {
            const age = parseInt(ageVal);
            if (isNaN(age)) return;
            if (age <= 14) ageBuckets['0–14']++;
            else if (age <= 24) ageBuckets['15–24']++;
            else if (age <= 44) ageBuckets['25–44']++;
            else if (age <= 59) ageBuckets['45–59']++;
            else ageBuckets['60+']++;
        }

        families.forEach(f => {
            if (f.demographics && f.demographics.age) addAge(f.demographics.age);
            (f.members || []).forEach(m => {
                if (m.demographics && m.demographics.age) addAge(m.demographics.age);
            });
        });

        const ageLabels = Object.keys(ageBuckets);
        const ageData = Object.values(ageBuckets);

        if (ageChartInstance) ageChartInstance.destroy();
        const ageCtx = document.getElementById('ageChart').getContext('2d');
        ageChartInstance = new Chart(ageCtx, {
            type: 'bar',
            data: {
                labels: ageLabels,
                datasets: [{
                    label: 'People',
                    data: ageData
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // ===== ALCOHOL DISTRIBUTION (heads only) =====
        const alcoholCounts = {};
        families.forEach(f => {
            const a = (f.addictions && f.addictions.alcohol) ? f.addictions.alcohol : 'Unknown';
            alcoholCounts[a] = (alcoholCounts[a] || 0) + 1;
        });

        const alcLabels = Object.keys(alcoholCounts);
        const alcData = Object.values(alcoholCounts);

        if (alcoholChartInstance) alcoholChartInstance.destroy();
        const alcCtx = document.getElementById('alcoholChart').getContext('2d');
        alcoholChartInstance = new Chart(alcCtx, {
            type: 'doughnut',
            data: {
                labels: alcLabels,
                datasets: [{
                    data: alcData
                }]
            }
        });

        // ===== SLEEP HOURS DISTRIBUTION =====
        const sleepBuckets = {
            '<6 hrs': 0,
            '6–8 hrs': 0,
            '>8 hrs': 0
        };

        families.forEach(f => {
            const lifestyle = f.lifestyle || {};
            const sh = lifestyle.sleep_hours;
            if (!sh) return;

            if (sh.includes('5') || sh.includes('4')) sleepBuckets['<6 hrs']++;
            else if (sh.includes('6') || sh.includes('7') || sh.includes('8')) sleepBuckets['6–8 hrs']++;
            else sleepBuckets['>8 hrs']++;
        });

        const sleepLabels = Object.keys(sleepBuckets);
        const sleepData = Object.values(sleepBuckets);

        if (sleepChartInstance) sleepChartInstance.destroy();
        const sleepCtx = document.getElementById('sleepChart').getContext('2d');
        sleepChartInstance = new Chart(sleepCtx, {
            type: 'pie',
            data: {
                labels: sleepLabels,
                datasets: [{
                    data: sleepData
                }]
            }
        });
    }

    document.addEventListener('DOMContentLoaded', loadData);
</script>

</body>
</html>
