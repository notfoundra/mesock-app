let macroInputs = {};
let saveTimer;
let daysOffCache = {}; // Local cache untuk hindari spam AJAX

// Ganti fungsi ini
function makeKey(delivery) {
    return delivery; 
}
function saveToServer(area, jarum) {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => {
        $.ajax({
            url: BASE_URL + "/saveMacroInputs",
            type: "POST",
            contentType: "application/json",
            // Pastikan Header CSRF disertakan di sini jika CI4 CSRF aktif
            data: JSON.stringify({
                area: area,
                jarum: jarum,
                inputs: macroInputs
            })
        });
    }, 500); 
}

$(document).ready(function () {
    resetTable();

    $('#areaSelect').on('change', function () {
        let area = $(this).val();
        $('#jarumSelect').html('<option value="">Select Jarum</option>');
        resetTable();
        
        if (!area) return;
        $('#jarumSelect').html('<option value="">Loading...</option>');

        $.ajax({
            url: BASE_URL + "/listJarum/" + area,
            type: "GET",
            dataType: "json",
            success: function (res) {
                let options = '<option value="">Select Jarum</option>';
                if (res.length > 0) {
                    res.forEach(v => { options += `<option value="${v}">${v}</option>`; });
                } else {
                    options += '<option value="">No Data</option>';
                }
                $('#jarumSelect').html(options);
            },
            error: function () {
                $('#jarumSelect').html('<option value="">Error loading data</option>');
            }
        });
    });

    $('#jarumSelect').on('change', function () {
        if (!checkFilter()) return;

        let area = $('#areaSelect').val();
        let jarum = $(this).val();

        setLoading();

        $.ajax({
            url: BASE_URL + "/getMacroPlan",
            type: "GET",
            data: { area, jarum },
            dataType: "json",
         success: function(res) {
                if (!res.status) {
                    resetTable();
                    return;
                }
                
                let serverInputs = res.data.inputs;

                // 1. Kalau bentuknya masih string (kayak di screenshot), kita parse dulu
                if (typeof serverInputs === 'string') {
                    try {
                        serverInputs = JSON.parse(serverInputs);
                    } catch (e) {
                        serverInputs = {}; // Kalau gagal parse, balikin kosong biar aman
                    }
                }
                
                // 2. Pastikan hasil akhirnya beneran Object {}
                if (Array.isArray(serverInputs) || typeof serverInputs !== 'object' || serverInputs === null) {
                    serverInputs = {};
                }
                
                macroInputs = serverInputs;
                
                renderSummary(res.data);
                renderTable(res.data);
            },
            error: function () { resetTable(); }
        });
    });

    // EVENT DELEGATION: Bind cuma sekali, performa jauh lebih enteng
    $(document).on('input', '.planning-mc, .add-qty', async function () {
        let row = $(this).closest('tr');
        await updateRowCalculation(row);
    });
});

// =========================
// FUNCTIONS
// =========================

function checkFilter() {
    let area = $('#areaSelect').val();
    let jarum = $('#jarumSelect').val();
    if (!area || !jarum) {
        resetTable();
        return false;
    }
    return true;
}

function resetTable() {
    $('#summaryBody').html(`<tr><td colspan="6" class="text-center text-muted">Silakan filter terlebih dahulu</td></tr>`);
    $('#macroTableBody').html(`<tr><td colspan="13" class="text-center text-muted">Silakan filter terlebih dahulu</td></tr>`);
}

function setLoading() {
    $('#summaryBody').html(`<tr><td colspan="6" class="text-center">Loading...</td></tr>`);
    $('#macroTableBody').html(`<tr><td colspan="13" class="text-center">Loading...</td></tr>`);
}

function renderSummary(d) {
    $('#summaryBody').html(`
        <tr>
            <td>${formatNumber(d.capacity)}</td>
            <td>${d.latestDate}</td>
            <td>${d.act_mc}</td>
            <td>${d.avg_prod}</td>
            <td>${d.plan_mc}</td>
            <td>${d.std_avg_prod}</td>
        </tr>
    `);
}

function formatNumber(num) {
    return Number(num).toLocaleString('id-ID');
}

function renderTable(res) {
    let list = res.listOrder || [];
    if (!list.length) {
        resetTable();
        return;
    }

    let html = '';
    let prevStopDate = res.latestDate;
    let planMcGlobal = Number(res.plan_mc) || 0;

    list.forEach((row, i) => {
        let delivery = row.delivery; // Contoh: "2026-04-29"
        let key = delivery; // Key-nya pakai delivery aja

        // Cek di cache JS: ada gak data buat tanggal delivery ini?
        let saved = macroInputs[key] || {};

        let qty = Number(row.qty) || 0;
        let sisa = Number(row.sisa) || 0;
        let smv = Number(row.smv) || 0;

        let target = smv > 0 ? (3600 / smv) * 0.80 : 0;
        
        // ==========================================
        // TARIK DATA DARI CACHE KE VARIABEL
        // ==========================================
        // Kalau saved.planning_mc ada isinya, pake itu. Kalau gak ada, pake planMcGlobal
        let planningMc = saved.planning_mc !== undefined ? saved.planning_mc : planMcGlobal;
        let addQty = saved.add_qty !== undefined ? saved.add_qty : 0;

        let ttlLeft = sisa + addQty;
        let dailyCapacity = planningMc * target;
        let prodDays = dailyCapacity > 0 ? Math.ceil(ttlLeft / dailyCapacity) : 0;

        let startDate = new Date(prevStopDate);
        let dateStop = addDays(startDate, prodDays);

        let stopDiff = diffDays(dateStop, new Date(delivery));
        let status = getStatus(stopDiff);

        // ==========================================
        // SUNTIK VARIABEL KE VALUE INPUT HTML
        // ==========================================
        html += `
         <tr data-key="${key}" data-smv="${smv}">
                <td>${delivery}</td>
                <td>${formatNumber(qty)}</td>
                <td>${Math.round(target)}</td>
                <td class="left-qty">${sisa}</td>
                
                <td>
                    <!-- SUNTIK KE SINI -->
                    <input type="number" class="form-control form-control-sm planning-mc" value="${planningMc}">
                </td>
                
                <td class="ttl-left">${ttlLeft}</td>
                <td class="prod-days">${prodDays}</td>
                <td class="days-off">0</td>
                <td class="date-stop">${formatDate(dateStop)}</td>
                <td class="stop-d">${stopDiff}</td>
                <td class="status">${status}</td>
                <td class="left-capacity">-</td>
                
                <td>
                    <!-- SUNTIK KE SINI -->
                    <input type="number" class="form-control form-control-sm add-qty" value="${addQty}">
                </td>
            </tr>
        `;

        prevStopDate = dateStop;
    });

    $('#macroTableBody').html(html);
}

// Logic kalkulasi dipisah biar bersih
async function updateRowCalculation(row) {
    let key = row.data('key');
    let planningMc = Number(row.find('.planning-mc').val()) || 0;
    let addQty = Number(row.find('.add-qty').val()) || 0;
    let sisa = Number(row.find('.left-qty').text()) || 0;
    let delivery = row.find('td:first').text();
    let smv = Number(row.data('smv')) || 0;

    let target = smv > 0 ? (3600 / smv) * 0.80 : 0;
    let ttlLeft = sisa + addQty;
    let dailyCapacity = planningMc * target;
    let prodDays = dailyCapacity > 0 ? Math.ceil(ttlLeft / dailyCapacity) : 0;

    let prevRow = row.prev();
    let startDate;

    if (prevRow.length && prevRow.find('.date-stop').text() !== '') {
        startDate = new Date(prevRow.find('.date-stop').text());
    } else {
        startDate = new Date($('#summaryBody td:nth-child(2)').text());
    }

    let dateStop = addDays(startDate, prodDays);

    let startFmt = formatDate(startDate);
    let stopFmt1 = formatDate(dateStop);

    let daysOff = await getDaysOffCached(startFmt, stopFmt1);
    dateStop = addDays(startDate, prodDays + daysOff);

    let stopFmt2 = formatDate(dateStop);
    let daysOff2 = await getDaysOffCached(startFmt, stopFmt2);
    
    if (daysOff2 !== daysOff) {
        daysOff = daysOff2;
        dateStop = addDays(startDate, prodDays + daysOff);
    }

    let stopDiff = diffDays(dateStop, new Date(delivery));

    row.find('.ttl-left').text(ttlLeft);
    row.find('.prod-days').text(prodDays);
    row.find('.days-off').text(daysOff);
    row.find('.date-stop').text(formatDate(dateStop));
    row.find('.stop-d').text(stopDiff);
    row.find('.status').html(getStatus(stopDiff));

    // Update global state & tembak server
    macroInputs[key] = { planning_mc: planningMc, add_qty: addQty };
    let area = $('#areaSelect').val();
    let jarum = $('#jarumSelect').val();
    saveToServer(area, jarum);
}

// Fungsi ini udah dioptimasi pake Memory Cache
function getDaysOffCached(start, stop) {
    let cacheKey = `${start}_${stop}`;
    if (daysOffCache[cacheKey] !== undefined) {
        return Promise.resolve(daysOffCache[cacheKey]);
    }

    return new Promise((resolve) => {
        $.ajax({
            url: BASE_URL + "/countDaysOff",
            type: "GET",
            data: { start, stop },
            success: function (res) {
                let val = Number(res) || 0;
                daysOffCache[cacheKey] = val; // Simpan ke local cache
                resolve(val);
            },
            error: function () { resolve(0); }
        });
    });
}

function addDays(date, days) {
    let d = new Date(date);
    d.setDate(d.getDate() + days);
    return d;
}

function diffDays(d1, d2) {
    return Math.floor((d2 - d1) / (1000 * 60 * 60 * 24));
}

function formatDate(date) {
    return new Date(date).toISOString().split('T')[0];
}

function getStatus(diff) {
    if (diff > 7) return '<span class="badge bg-success">SAFE</span>';
    if (diff >= 3) return '<span class="badge bg-warning text-dark">CLOSE</span>';
    return '<span class="badge bg-danger">NOT SAFE</span>';
}