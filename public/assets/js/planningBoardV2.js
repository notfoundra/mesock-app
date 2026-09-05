console.log('hi');

let holidayMap = {};
async function fetchLibur(start, stop) {
    const res = await fetch(`${aps_base_url}/getListLibur`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            startDate: start,
            endDate: stop
        })
    });

    const data = await res.json();

    const list = data.data ?? [];

    // convert ke map biar O(1)
    holidayMap = {};
    list.forEach(d => {
        holidayMap[d.start] = d.title ?? 'Libur';
    });

    return list;
}
/* ===============================
   INIT CALENDAR
================================ */
document.addEventListener('DOMContentLoaded', function () {
    let lastRequestId = 0;
    calendar = new FullCalendar.Calendar(
        document.getElementById('calendar'), {
        schedulerLicenseKey: 'CC-Attribution-NonCommercial-NoDerivatives',
        initialView: 'resourceTimeline',
        height: '100%',
        expandRows: true,
        resourceAreaWidth: 180,
        resourceAreaHeaderContent: 'Area / Jarum',
        resourceOrder: ['sort_index', 'no_mc'],

        slotDuration: {
            days: 1
        },
        slotLabelInterval: {
            days: 1
        },
        slotMinWidth: 90,

        slotLabelFormat: [{
            weekday: 'short',
            day: '2-digit',
            month: 'short'
        }],

        slotLabelDidMount(info) {

            const dateStr = info.date.toISOString().slice(0, 10);

            if (holidayMap[dateStr]) {
                info.el.style.background = '#ff8181';
                info.el.style.color = '#fff';
                info.el.title = holidayMap[dateStr];
            }

            if (info.date.getDay() === 0) {
                info.el.style.color = '#dc2626';
                info.el.style.fontWeight = '700';
            }
        },

        /* BODY */
        slotLaneDidMount(info) {

            const dateStr = info.date.toISOString().slice(0, 10);
            const day = info.date.getDay();

            const today = new Date();
            const isToday =
                info.date.getFullYear() === today.getFullYear() &&
                info.date.getMonth() === today.getMonth() &&
                info.date.getDate() === today.getDate();

            if (holidayMap[dateStr]) {
                info.el.style.background = '#ff8181';
                return;
            }

            if (isToday) {
                info.el.style.backgroundColor = '#eef2ff';
            } else if (day === 0) {
                info.el.style.backgroundColor = '#fdf2f2';
            } else if (day === 6) {
                info.el.style.backgroundColor = '#f8fafc';
            }
        },

        visibleRange: {
            start: startDate,
            end: safeEnd
        },

        resources: [],
        events: function (info, successCallback, failureCallback) {
            $.ajax({
                url: base_url + 'api/events',
                data: {
                    start: info.startStr,
                    stop: info.endStr,
                    area: $('#filterArea').val(),
                    jarum: $('#filterJarum').val(),
                    buyer: $('#filterBuyer').val()
                },
                success: function (res) {
                    const cleanEvents = res.filter(e => e.title !== null);
                    successCallback(cleanEvents);
                },
                error: function () {
                    failureCallback();
                }
            });
        },

        editable: true,
        selectable: true,
        selectMirror: true,
        nowIndicator: true,
        lazyFetching: true,
        progressiveEventRendering: true,

       eventContent: function (arg) {
    if (arg.event.extendedProps?.type === 'holiday') {
        return { html: `<b>LIBUR</b>` };
    }
    if (arg.event.extendedProps?.isBg) {
        return { html: `<div class="bg-mc-avail-content">${arg.event.title}</div>` };
    }

    const props = arg.event.extendedProps;
    
    // 1. DATA PREP
    const model = arg.event.title || props.model || '';
    const inisial = props.inisial ? `<span class="plan-inisial">(${props.inisial})</span>` : '';
    const delivery = props.delivery || '';
    const jumMc = props.jum_mc ? `${props.jum_mc} MC` : '0 MC';
    
    // 2. HITUNG PROGRESS (Qty Prod vs Qty Order)
    const prod = Number(props.qty_prod) || 0;
    const order = Number(props.qty_order) || 1; // Cegah Infinity / div by zero
    let progressPct = Math.round((prod / order) * 100);
    progressPct = progressPct > 100 ? 100 : progressPct; // Mentokin di 100%

    // 3. DETEKSI ANOMALI / WARNING (Ini fitur penyelamat Planner)
    const isLate = (props.estimasi || '').toLowerCase() === 'late';
    const isNotSafe = (props.order_status || '').toLowerCase() === 'not safe';
    
    let warningBadge = '';
    if (isLate || isNotSafe) {
        const telatHari = props.selisih_hari ? `-${props.selisih_hari}d` : '';
        warningBadge = `<span class="plan-badge-danger" title="Estimasi Telat/Not Safe!">⚠️ ${telatHari}</span>`;
    }

    // 4. RENDER HTML
    return {
        html: `
        <div class="plan-bar-wrapper">
            <div class="plan-row-top">
                <div class="plan-title-area">${model} ${inisial}</div>
                <div class="plan-deadline-area">${warningBadge} <span>📅 ${delivery}</span></div>
            </div>
            
            <div class="plan-row-bot">
                <div class="plan-mc-area"><i class="fas fa-cogs"></i> ${jumMc}</div>
                <div class="plan-prog-area">${progressPct}%</div>
            </div>

            <!-- Slim Progress Bar di dasar kotak -->
            <div class="plan-slim-bar">
                <div class="plan-slim-fill" style="width: ${progressPct}%;"></div>
            </div>
        </div>
        `
    };
},
        eventDidMount: function (info) {
            if (info.event.extendedProps?.type === 'holiday') {
                info.el.classList.add('fc-event-holiday');
                return;
            }


            const statusText = (info.event.extendedProps?.plan_status ?? 'planned').toString().toLowerCase();

            info.el.classList.remove(
                'fc-event-delay',
                'fc-event-running',
                'fc-event-complete',
                'fc-event-planned'
            );

            if (statusText.includes('waiting') || statusText.includes('delay')) {
                info.el.classList.add('fc-event-delay');
            } else if (statusText.includes('running')) {
                info.el.classList.add('fc-event-running');
            } else if (statusText.includes('finished') || statusText.includes('complete')) {
                info.el.classList.add('fc-event-complete');
            } else {
                info.el.classList.add('fc-event-planned');
            }
        },

        eventDataTransform: function (eventData) {
            if (eventData.title === null) {
                return null;
            }
            return eventData;
        },

        select(info) {
            if (!info.resource) return;

            openAddPlanningModal({
                mesin: info.resource.id,
                start: info.startStr,
                end: info.endStr
            });
        },

        eventClick: function (info) {
            showEventDetails(info.event);
        },

        eventDrop: function (info) {
            const start = info.event.start;
            const end = info.event.end ?? info.event.start;

            if (end < start) {
                alert('Tanggal tidak valid');
                info.revert();
                return;
            }

            if (info.oldResource && info.newResource && info.oldResource.id !== info.newResource.id) {
                alert('Tidak dapat memindahkan grup plan ke mesin/area lain secara langsung.');
                info.revert();
                return;
            }

            updateEvent(info);
        },

        eventResize: updateEvent,
    }
    );

    calendar.render();

    $('#startDate').val(startDate);
    $('#endDate').val(stopDate);
});

function dz(val) {
    if (val === 0 || val === '0' || Number(val) === 0) {
        return '0.00 Dz';
    }
    return Number.isFinite(Number(val)) ? `${parseFloat(val).toFixed(2)} Dz` : '-';
}

function pcs(val) {
    if (val === 0 || val === '0' || Number(val) === 0) {
        return '0 pcs';
    }
    return Number.isFinite(Number(val)) ? `${parseInt(val, 10)} pcs` : '-';
}

function showLoading() {
    $('#loading-spinner').removeClass('d-none');
}

function hideLoading() {
    $('#loading-spinner').addClass('d-none');
}

function showNonBlockingError(msg) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: msg,
            timer: 1800,
            showConfirmButton: false
        });
    } else {
        console.error(msg);
    }
}

function fetchJsonSafe(url, opts = {}) {
    return fetch(url, {
        ...opts,
        cache: 'no-store',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(opts.headers || {})
        }
    }).then(async res => {
        const text = await res.text();
        const cleaned = (text ?? '').replace(/^\uFEFF/, '').trim();
        if (!cleaned) {
            throw new Error('Empty response');
        }
        if (cleaned.startsWith('<!DOCTYPE') || cleaned.startsWith('<html')) {
            throw new Error('Session expired');
        }
        try {
            return JSON.parse(cleaned);
        } catch {
            throw new Error('Invalid JSON');
        }
    });
}

function updateOrderStatusBadge(text) {
    const el = document.getElementById('plan_status');
    const bar = document.getElementById('order_progress');
    if (!el) return;

    const raw = (text ?? el.textContent ?? '').toString().trim();
    const value = raw.toLowerCase();

    el.classList.remove(
        'bg-primary',
        'bg-success',
        'bg-warning',
        'bg-danger',
        'bg-secondary',
        'bg-info'
    );

    if (bar) {
        bar.classList.remove(
            'is-planned',
            'is-running',
            'is-delay',
            'is-complete',
            'is-early'
        );
    }

    let label = raw;

    if (value.includes('waiting') || value.includes('delay')) {
        el.classList.add('bg-danger');
        if (bar) bar.classList.add('is-delay');
    } else if (value.includes('running')) {
        el.classList.add('bg-success');
        if (bar) bar.classList.add('is-running');
    } else if (value.includes('finished') || value.includes('complete')) {
        el.classList.add('bg-secondary');
        if (bar) bar.classList.add('is-complete');
    } else if (value.includes('planned')) {
        el.classList.add('bg-primary');
        if (bar) bar.classList.add('is-planned');
    } else if (value.includes('early')) {
        el.classList.add('bg-warning');
        if (bar) bar.classList.add('is-early');
    } else {
        el.classList.add('bg-info');
    }

    el.textContent = label;
}

function loadMaterialStock(noModel, styleSize) {

    $('#material_detail').html(`
            <tr>
                <td colspan="4" class="text-center text-muted">
                    Loading material...
                </td>
            </tr>
        `);

    fetch(`${material_api_url}/materialStock?no_model=${noModel}&style_size=${styleSize}`)
        .then(res => res.json())
        .then(res => {

            if (!res.success || res.data.length === 0) {
                $('#material_detail').html(`
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Tidak ada data material
                            </td>
                        </tr>
                    `);
                return;
            }

            let html = '';
            let hasRisk = false;

            res.data.forEach(item => {

                // contoh hitung stock
                let available =
                    (parseFloat(item.total_kgs_stock_awal) || 0) +
                    (parseFloat(item.total_kgs_in_out) || 0);

                let required = parseFloat(item.qty_required || 0);

                let statusText = 'Ready';
                let statusClass = 'text-success';
                let icon = 'fa-check-circle';

                if (available <= 0) {
                    statusText = 'Not Ready';
                    statusClass = 'text-danger';
                    icon = 'fa-times-circle';
                    hasRisk = true;
                } else if (available < required) {
                    statusText = 'Partial';
                    statusClass = 'text-warning';
                    icon = 'fa-exclamation-triangle';
                    hasRisk = true;
                }

                html += `
                        <tr>
                            <td>
                                <div class="fw-semibold">${item.item_type}</div>
                                <small class="text-muted">
                                    ${item.color || '-'} / ${item.kode_warna || '-'}
                                </small>
                            </td>
                            <td class="text-end">${required} kg</td>
                            <td class="text-end">${available.toFixed(2)} kg</td>
                            <td class="${statusClass} fw-semibold">
                                <i class="fas ${icon} me-1"></i> ${statusText}
                            </td>
                        </tr>
                    `;
            });

            $('#material_detail').html(html);

            // warning APS
            if (hasRisk) {
                $('#material_warning').removeClass('d-none');
            } else {
                $('#material_warning').addClass('d-none');
            }

            $('#material_last_update').text(new Date().toLocaleString());

        })
        .catch(err => {
            console.error(err);
            $('#material_detail').html(`
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            Gagal load material
                        </td>
                    </tr>
                `);
        });
}

function syncProgressLabel() {
    const bar = document.getElementById('order_progress');
    const label = document.getElementById('order_progress_label');
    if (!bar || !label) return;
    const text = (bar.textContent || '').trim();
    const width = bar.style.width || '0%';
    label.textContent = text || width;
}

document.addEventListener('DOMContentLoaded', () => {
    const statusEl = document.getElementById('plan_status');
    const progressEl = document.getElementById('order_progress');

    let lastStatus = '';
    let lastProgress = '';

    setInterval(() => {
        if (statusEl) {
            const current = statusEl.textContent.trim();
            if (current !== lastStatus) {
                lastStatus = current;
                updateOrderStatusBadge(current);
            }
        }

        if (progressEl) {
            const current = progressEl.textContent.trim();
            if (current !== lastProgress) {
                lastProgress = current;
                syncProgressLabel(current);
            }
        }
    }, 500); // aman, bisa 1000ms kalau mau
});


/* ===============================
   UPDATE EVENT
================================ */
function updateEvent(info) {
    const e = info.event;
    // console.log(e);

    showLoading();
    calendar.setOption('editable', false); // 🔒 lock sementara

    $.post(base_url + 'api/updateTimeline', {
        id: e.id,
        // Gunakan rawResourceId karena resourceId sekarang adalah groupId (jarum|area)
        resourceId: e.extendedProps.rawResourceId,
        start: e.startStr,
        end: e.endStr ?? e.startStr
    })
        .done(res => {
            if (!res || res.success !== true) throw new Error();
            clearRangeCache();
            reloadCalendar(true);
        })
        .fail((xhr, status, err) => {
            info.revert();
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Gagal update planning: ' + (xhr.responseJSON?.message || err)
            });
        })
        .always(() => {
            hideLoading();
            calendar.setOption('editable', true); // 🔓 buka lagi
        });
}

/* ===============================
   DATE RANGE APPLY
================================ */
const __rangeCache = new Map();

function buildRangeKey(params) {
    return [
        params.start,
        params.stop,
        params.area,
        params.jarum ?? '',
        params.buyer ?? ''
    ].join('|');
}

function fetchResources(params, opts = {}) {
    const key = 'res_' + buildRangeKey(params);
    if (!opts.force && __rangeCache.has(key)) {
        return Promise.resolve(__rangeCache.get(key));
    }

    const url = new URL(base_url + 'api/resources');
    Object.keys(params).forEach(k => url.searchParams.append(k, params[k]));

    return fetchJsonSafe(url).then(res => {
        __rangeCache.set(key, res);
        return res;
    });
}



function clearRangeCache() {
    __rangeCache.clear();
}

$('#btnApply').on('click', async function () {

    const start = $('#startDate').val();
    const stop = $('#endDate').val();
    const area = $('#filterArea').val();
    const loading = document.getElementById('loading-spinner');

    if (!start || !stop || !area) {
        alert('Area, Tanggal start & stop wajib diisi');
        return;
    }

    loading.classList.remove('d-none');

    try {

        const params = {
            start,
            stop,
            area,
            jarum: $('#filterJarum').val(),
            buyer: $('#filterBuyer').val()
        };

        const resourcesPromise = fetchResources(params);

        const liburPromise = fetchLibur(start, stop);

        const [resources, libur] = await Promise.all([
            resourcesPromise,
            liburPromise
        ]);
        holidayMap = {};
        if (Array.isArray(libur)) {
            libur.forEach(l => {
                holidayMap[l.start] = l.title ?? 'Libur';
            });
        }

        /* RESET */
        calendar.removeAllEvents();

        // hapus semua resource lama
        calendar.getResources().forEach(r => r.remove());

        // add resource baru (gunakan grouped untuk kalender)
        if (resources && resources.grouped && resources.grouped.length > 0) {
            loading.classList.add('d-none')
            resources.grouped.forEach(r => calendar.addResource(r));
            // simpan raw resources di global variable jika diperlukan
            window.rawMachineResources = resources.raw;
        }


        if (libur?.length) {
            const liburEvents = libur.map(l => ({
                start: l.start,
                end: l.end,
                display: 'background',
                className: 'libur',
                type: 'holiday',
                title: l.title
            }));

            calendar.addEventSource(liburEvents);
        }



        // 3️⃣ Update calendar
        hideEmptyState();
        const endRange = addOneDay(stop);
        if (!endRange) {
            loading.classList.add('d-none')

            alert('Tanggal stop tidak valid');
            return;
        }

        calendar.setOption('visibleRange', {

            start: start,
            end: endRange
        });

        calendar.gotoDate(start);
        calendar.refetchEvents();

        // penting buat rerender warna libur
        calendar.render();

    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Gagal memuat data planning'
        });
    } finally {
        loading.classList.add('d-none');
    }
});


function showEmptyState(text = 'Data tidak ditemukan') {
    $('#calendarEmpty strong').text(text);
    $('#calendarEmpty').show();
}

function hideEmptyState() {
    $('#calendarEmpty').hide();
}


/* ===============================
   APS MODE
================================ */
const btnAps = document.getElementById('btnApsFullscreen');

btnAps.onclick = () => {
    const elem = document.documentElement; // fullscreen seluruh halaman
    // kalau mau calendar aja: document.getElementById('calendar')

    if (!document.fullscreenElement) {
        elem.requestFullscreen().then(() => {
            // document.body.classList.add('aps-mode');
            calendar.updateSize();
            btnAps.innerHTML = '<i class="fas fa-compress"></i>';
        });
    } else {
        document.exitFullscreen().then(() => {
            document.body.classList.remove('aps-mode');
            calendar.updateSize();
            btnAps.innerHTML = '<i class="fas fa-expand"></i>';
        });
    }
};

/* ===============================
   UTILS
================================ */
function addOneDay(dateStr) {

    if (!dateStr) return null;

    const d = new Date(dateStr);

    if (isNaN(d.getTime())) {
        console.warn('Invalid date passed to addOneDay:', dateStr);
        return null;
    }

    d.setDate(d.getDate() + 1);
    return d.toISOString().split('T')[0];
}
document.querySelector('.btn-save')
    ?.addEventListener('click', savePlanning);

function renderErrorList(errors = []) {
    if (!errors.length) return '';

    return `
            <div style="text-align:left; max-height:250px; overflow:auto;">
                <ul>
                    ${errors.map(e => `<li>${e}</li>`).join('')}
                </ul>
            </div>
        `;
}

function savePlanning() {
    const formData = new FormData();
    const header = document.querySelector('.planning-header');

    const area = header.querySelector('.select-area')?.value || '';
    const mastermodel = header.querySelector('.select-order')?.value || '';
    const jarum = header.querySelector('.jarum')?.value || '';
    const delivery = header.querySelector('.select-delivery')?.value || '';
    const idDetail = header.querySelector('.id_detail')?.value || '';

    document.querySelectorAll('.planning-block').forEach((block, i) => {

        formData.append(
            `style[${i}]`,
            block.querySelector('input[name="style[]"]')?.value || ''
        );

        formData.append(`area[${i}]`, area);
        formData.append(`jarum[${i}]`, jarum);
        formData.append(`id_detail[${i}]`, idDetail);
        formData.append(`mastermodel[${i}]`, mastermodel);
        formData.append(`delivery[${i}]`, delivery);

        formData.append(
            `target[${i}]`,
            block.querySelector('.input-target')?.value
        );

        block.querySelectorAll('.mesin-row-item').forEach((row, j) => {
            formData.append(
                `no_mc[${i}][${j}]`,
                row.querySelector('input[name^="no_mc"]')?.value || ''
            );
            formData.append(
                `start[${i}][${j}]`,
                row.querySelector('input[name^="start"]')?.value
            );
            formData.append(
                `stop[${i}][${j}]`,
                row.querySelector('input[name^="stop"]')?.value
            );
            formData.append(
                `est[${i}][${j}]`,
                row.querySelector('input[name^="est"]')?.value
            );
        });
    });

    // loading Swal
    Swal.fire({
        title: 'Menyimpan...',
        text: 'Mohon tunggu',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`${base_url}api/savePlanningBoard`, {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(res => {

            // ===== GAGAL TOTAL =====
            if (res.status !== 'ok') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal menyimpan planning',
                    html: `
                        <p>${res.message || 'Terjadi kesalahan'}</p>
                        ${renderErrorList(res.errors)}
                    `,
                    width: 700
                });
                return;
            }

            // ===== SUKSES SEBAGIAN =====
            if (res.failed_count && res.failed_count > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Planning tersimpan sebagian',
                    html: `
                        <p>
                            <b>${res.success_count}</b> data berhasil disimpan<br>
                            <b>${res.failed_count}</b> data gagal
                        </p>
                        ${renderErrorList(res.errors)}
                    `,
                    width: 700,
                    confirmButtonText: 'OK'
                }).then(() => {
                    clearRangeCache();
                    reloadCalendar(true);
                });
            }
            // ===== SUKSES FULL =====
            else {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: `${res.success_count} planning berhasil disimpan`,
                    timer: 1800,
                    showConfirmButton: false,
                    timerProgressBar: true
                }).then(() => {
                    clearRangeCache();
                    reloadCalendar(true);
                });
            }

            // ===== RESET MODAL =====
            const modalEl = document.getElementById('modalAddPlan');
            if (modalEl) {
                bootstrap.Modal.getInstance(modalEl)?.hide();

                setTimeout(() => {
                    modalEl.querySelectorAll('input, select, textarea').forEach(el => {
                        if (el.type === 'checkbox' || el.type === 'radio') {
                            el.checked = false;
                        } else {
                            el.value = '';
                        }
                    });

                    $(modalEl).find('select').val(null).trigger('change');

                    modalEl.querySelectorAll('.rec-machines').forEach(el => {
                        el.textContent = 'Mesin';
                    });

                    modalEl.querySelectorAll('.total-est').forEach(el => {
                        el.textContent = 'Total Estimasi: 0';
                    });
                }, 300);
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Terjadi kesalahan saat menyimpan planning'
            });
        });
}

function saveEdit() {

    const idInput = document.getElementById('id_plan_edit');
    if (!idInput) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'ID planning tidak ditemukan di modal'
        });
        return;
    }

    const formData = new FormData();
    formData.append('id_plan', idInput.value);
    formData.append('id_aps', document.getElementById('id_aps_edit').value);
    formData.append('no_mc', document.getElementById('no_mc_edit').value);
    formData.append('jarum', document.getElementById('jarum_edit').value);
    formData.append('start', document.getElementById('start_edit').value);
    formData.append('stop', document.getElementById('stop_edit').value);
    formData.append('est', document.getElementById('est_edit').value);
    formData.append('target', document.getElementById('target_edit').value);
    formData.append('jum_mc', document.getElementById('jum_mc_edit').value);

    fetch(`${base_url}api/savePlanEdit`, {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Gagal update planning'
                });
                return;
            }

            reloadCalendar();

            const modalEl = document.getElementById('modalDetailPlan');
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal?.hide();

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Planning berhasil diupdate',
                timer: 1500,
                showConfirmButton: false
            });
        })
        .done(() => {
            // no-op: handled in success branch
        })
        .catch(err => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Server Error',
                text: 'Terjadi kesalahan saat update planning'
            });
        });
}

function deletePlan() {

    const idPlan = $('#id_plan_edit').val();

    if (!idPlan) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Data delete tidak lengkap'
        });
        return;
    }

    Swal.fire({
        title: 'Yakin mau hapus?',
        text: 'Planning area/jarum ini akan dihapus!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then(result => {

        if (!result.isConfirmed) return;

        const formData = new FormData();
        formData.append('id_plan', idPlan);

        fetch(`${base_url}api/deletePlanningBoard`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(res => {

                if (!res.success) {
                    Swal.fire('Gagal', res.message || 'Gagal hapus planning', 'error');
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Terhapus',
                    timer: 1200,
                    showConfirmButton: false
                });

                clearRangeCache();
                reloadCalendar(true);
                bootstrap.Modal.getInstance(
                    document.getElementById('modalDetailPlan')
                )?.hide();
            });
    });
}

function showEventDetails(event) {
    const props = event.extendedProps;
    console.log('data nih ', props);

    const formatYMD = (dateObj) => {
        if (!dateObj) return '';
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    };

    const start = event.start ? formatYMD(event.start) : '';

    let stop = start;
    if (event.end) {
        const endObj = new Date(event.end);
        endObj.setDate(endObj.getDate() - 1);
        stop = formatYMD(endObj);
    }
    // Set modal title
    $('#modalDetailTitle').text('Detail Planning - ' + (props.inisial || event.title));

    // Set basic info
    $('#id_plan_edit').val(event.id);
    $('#id_aps_edit').val(props.aps);
    $('#jarum_edit').val(props.jarum);
    $('#original_est_edit').val(props.est || 0);
    $('#original_sisa_edit').val(props.sisa || 0);



    // Set edit form values
    $('#no_mc_edit').val(props.no_mc || '-');
    $('#jarum_edit').val(props.jarum || '');
    $('#target_edit').val(props.target || 0);
    $('#jum_mc_edit').val(props.jum_mc || 0);
    $('#jum_mc_suggested').text(parseFloat(props.jum_mc || 0).toFixed(2));
    $('#start_edit').val(start);
    $('#stop_edit').val(stop);
    $('#d_model_edit').val(props.model || '-');
    $('#keterangan_edit').val(props.keterangan || '');
    // type=date
    $('#d_delivery_edit').val(props.delivery_date || '-');
    $('#qty_edit').val(props.qty_order || '-');
    $('#remaining_qty_edit').val(props.sisa || '0');

    let orderQty = parseFloat(props.qty_order) || 0;
    let sisaQty = parseFloat(props.sisa) || 0;
    $('#unplanned_qty_edit').val(orderQty - sisaQty > 0 ? (orderQty - sisaQty) : 0);
    $('#target_100_edit').val('');
    $('#percentage_edit').val('');

    // Fetch Target 100% (SMV) from getModelData
    if (props.delivery_date && props.model && props.jarum && props.area) {
        $.ajax({
            url: base_url + 'aps/getModelData',
            type: 'GET',
            dataType: 'json',
            data: {
                delivery: props.delivery_date,
                model: props.model,
                jarum: props.jarum,
                area: props.area
            },
            success: function (response) {
                const smv = response.smv || (response.result && response.result.smv);
                if (smv) {
                    $('#target_100_edit').val(smv);
                    updatePercentage();
                }
            }
        });
    }

    // Populate detail view directly from props (matches Model data exactly)
    $('#d_model').text(props.model || '-');
    $('#d_inisial').text(props.inisial || '-');
    $('#d_delivery').text(props.delivery_date || '-');

    // Show Area | Jarum on header
    $('#header_area_jarum').text((props.area || '') + ' | ' + (props.jarum || ''));

    $('#plan_start').text(start || '-');
    $('#plan_stop').text(stop || '-');

    $('#target_produksi').text(dz(props.est) || '-');
    $('#total_produksi').text(dz(props.qty_prod) || '-');

    let progressPct = 0;
    if (props.est > 0) {
        progressPct = (props.qty_prod / props.est) * 100;
        if (progressPct > 100) progressPct = 100;
    }

    const progressEl = document.getElementById('order_progress');
    if (progressEl) {
        progressEl.style.width = `${progressPct.toFixed(0)}%`;
        progressEl.textContent = `${progressPct.toFixed(0)}%`;
    }
    $('#order_progress_label').text(`${progressPct.toFixed(0)}%`);

    updateOrderStatusBadge(props.plan_status || 'Planned');

    // Pace Produksi (Estimasi)
    const paceEl = document.getElementById('estimasi_pace');
    if (paceEl) {
        let paceText = props.estimasi || 'Unknown';
        if (props.selisih_hari > 0) {
            paceText += ` (${props.selisih_hari} Hari)`;
        }
        paceEl.textContent = paceText;
        paceEl.className = 'badge';
        const paceLower = paceText.toLowerCase();
        if (paceLower.includes('early')) paceEl.classList.add('bg-success');
        else if (paceLower.includes('late')) paceEl.classList.add('bg-danger');
        else if (paceLower.includes('track')) paceEl.classList.add('bg-primary');
        else if (paceLower.includes('aman') || paceLower.includes('finished')) paceEl.classList.add('bg-info');
        else paceEl.classList.add('bg-secondary');
    }

    // Order Safety (Jarak ke Delivery)
    const safetyEl = document.getElementById('order_safety');
    if (safetyEl) {
        let safetyText = props.order_status || 'Unknown';
        if (props.jarak_delivery !== undefined) {
            safetyText += ` (Jarak: ${props.jarak_delivery} Hari)`;
        }
        safetyEl.textContent = safetyText;
        safetyEl.className = 'badge';
        const safetyLower = safetyText.toLowerCase();
        if (safetyLower.includes('not safe')) safetyEl.classList.add('bg-danger');
        else if (safetyLower.includes('safe')) safetyEl.classList.add('bg-success');
        else safetyEl.classList.add('bg-secondary');
    }

    // Calculate and set estimation
    calculateEst();

    // Load production data (only for material stock, BS, and detailed order qty)
    loadProductionData(props.aps,
        start,
        stop,
        props.target, props.est
    );

    // Show modal
    const modal = new bootstrap.Modal('#modalDetailPlan');
    modal.show();
}

function loadProductionData(idaps, start, stop, target, est) {
    if (!idaps) return;
    $.ajax({
        url: base_url + '/aps/getQtyProduksiDanBSByStyle?idaps=' + idaps + '&start=' + start + '&stop=' + stop + '&target=' + target + '&est=' + est,
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status !== 'success' || !response.data) return;

            const d = response.data;
            $('#d_qty').text(d.qty_order_display ?? '-');
            $('#d_sisa').text(d.sisa_order_display ?? '-');
            $('#d_plus_pk').text(d.plus_pk_display ?? '-');

            $('#est_selesai').text((d.est_selesai ?? '').toString().substring(0, 10) || '-');
            $('#act_start').text((d.act_start ?? '').toString().substring(0, 10) || '-');

            $('#modal_d_prod').text(d.qty_produksi_display ?? '-');
            $('#modal_d_bs').text(d.qty_bs_display ?? '-');

            // Update input values in Edit Planning tab
            $('#qty_edit').val(d.qty_order_display ?? '-');
            $('#remaining_qty_edit').val(d.sisa_order_display ?? '0');

            loadMaterialStock(d.mastermodel, d.size);
        }
    });
}

async function reloadCalendar(force = false) {
    const view = calendar.view;
    const start = view.activeStart.toISOString().slice(0, 10);

    // ⬇️ activeEnd itu EXCLUSIVE, jadi harus -1 hari
    const endDate = new Date(view.activeEnd);
    endDate.setDate(endDate.getDate() + 1);
    const stop = endDate.toISOString().slice(0, 10);
    let loading = document.getElementById('loading-spinner');

    loading.classList.remove('d-none');

    try {
        const params = {
            start,
            stop,
            area: $('#filterArea').val(),
            jarum: $('#filterJarum').val(),
            buyer: $('#filterBuyer').val()
        };

        const resourcesPromise = fetchResources(params, { force });

        const liburPromise = fetchLibur(start, stop);

        const [resources, libur] = await Promise.all([
            resourcesPromise,
            liburPromise
        ]);

        calendar.removeAllEvents();
        calendar.getResources().forEach(r => r.remove());

        if (resources && resources.grouped && resources.grouped.length > 0) {
            resources.grouped.forEach(r => calendar.addResource(r));
            window.rawMachineResources = resources.raw;
        }



        if (libur?.length) {
            const liburEvents = libur.map(l => ({
                start: l.start,
                end: l.end,
                display: 'background',
                className: 'libur',
                type: 'holiday',
                title: l.title
            }));
            calendar.addEventSource(liburEvents);
        }

        calendar.refetchEvents();
        calendar.render();

    } catch (err) {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Gagal memuat ulang data planning'
        });
    } finally {
        loading.classList.add('d-none');
    }
}

function calculateEst() {
    const startVal = $('#start_edit').val();
    const stopVal = $('#stop_edit').val();
    const targetVal = parseFloat($('#target_edit').val()) || 0;
    const mcVal = parseFloat($('#jum_mc_edit').val()) || 0;

    if (!startVal || !stopVal) {
        $('#days_edit').val('');
        $('#est_edit').val('');
        $('#holiday_count_edit').val('');
        return;
    }

    const start = new Date(startVal);
    const stop = new Date(stopVal);

    if (stop < start) {
        $('#days_edit').val('');
        $('#est_edit').val('');
        $('#holiday_count_edit').val('');
        return;
    }

    // Hitung working days eksklusif dari holidayMap
    let workingDays = 0;
    let holidayCount = 0;
    let currentDate = new Date(start);

    while (currentDate <= stop) {
        let dateString = currentDate.toISOString().slice(0, 10);
        if (!holidayMap[dateString]) {
            workingDays++;
        } else {
            holidayCount++;
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }

    $('#days_edit').val(workingDays);
    $('#holiday_count_edit').val(holidayCount);

    // Dinamic Machine Suggestion
    const originalEst = parseFloat($('#original_est_edit').val()) || 0;
    const originalSisa = parseFloat($('#original_sisa_edit').val()) || 0;
    const totalToProduce = originalEst + originalSisa;

    if (targetVal > 0 && workingDays > 0) {
        const suggested = originalEst / (targetVal * workingDays);
        $('#jum_mc_suggested').text(suggested.toFixed(2));
    } else {
        $('#jum_mc_suggested').text('0.00');
    }

    if (targetVal > 0 && mcVal > 0) {
        const est = workingDays * targetVal * mcVal;
        $('#est_edit').val(est);
    } else {
        $('#est_edit').val('');
    }
}

function updatePercentage() {
    var target100 = parseFloat($('#target_100_edit').val());
    var calculatedTarget = parseFloat($('#target_edit').val());

    if (!isNaN(target100) && target100 > 0 && !isNaN(calculatedTarget)) {
        var newPercentage = ((calculatedTarget / target100) * 100).toFixed(2);
        $('#percentage_edit').val(newPercentage);
    } else {
        $('#percentage_edit').val('');
    }
}

$('#start_edit, #stop_edit').on('change', calculateEst);
$('#target_edit, #jum_mc_edit').on('input', function () {
    calculateEst();
    updatePercentage();
});

/* ================================
CACHE LIBUR PER PLANNING
================================ */
const planningLiburCache = new WeakMap();

/* ================================
   AMBIL RANGE SEMUA MESIN
================================ */
function getPlanningRange(block) {
    let minStart = null;
    let maxStop = null;

    block.querySelectorAll('.mesin-row-item').forEach(row => {
        const start = row.querySelector('.input-start')?.value;
        const stop = row.querySelector('.input-stop')?.value;

        if (!start || !stop) return;

        if (!minStart || start < minStart) minStart = start;
        if (!maxStop || stop > maxStop) maxStop = stop;
    });

    if (!minStart || !maxStop) return null;

    return {
        minStart,
        maxStop
    };
}

/* ================================
   FETCH LIST LIBUR SEKALI
================================ */
async function fetchPlanningLibur(block) {
    const range = getPlanningRange(block);
    if (!range) return [];

    if (planningLiburCache.has(block)) {
        return planningLiburCache.get(block);
    }

    try {
        const res = await fetch(`${aps_base_url}/getListLibur`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                startDate: range.minStart,
                endDate: range.maxStop
            })
        });

        const data = await res.json();

        const list = data.data || [];

        planningLiburCache.set(block, list);

        return list;
    } catch (err) {
        console.error("Gagal ambil list libur", err);
        return [];
    }
}

/* ================================
   HITUNG HARI KERJA
================================ */
function hitungHariKerja(start, stop, listLibur) {
    let startDate = new Date(start);
    let stopDate = new Date(stop);

    let total = 0;

    while (startDate <= stopDate) {
        const dateStr = startDate.toISOString().split('T')[0];

        const isLibur = listLibur.some(l => l.start === dateStr);

        if (!isLibur) total++;

        startDate.setDate(startDate.getDate() + 1);
    }

    return total;
}

/* ================================
   RECALC ROW MESIN
================================ */
async function recalcRow(row, block) {
    const start = row.querySelector('.input-start')?.value;
    const stop = row.querySelector('.input-stop')?.value;

    if (!start || !stop) return;

    const listLibur = await fetchPlanningLibur(block);

    const hariKerja = hitungHariKerja(start, stop, listLibur);

    const labelHari = row.querySelector('.total-hari');
    if (labelHari) {
        labelHari.textContent = `${hariKerja} day`;
    }

    const target = Number(block.querySelector('.input-target')?.value);
    const estInput = row.querySelector('input[name^="est"]');

    if (target && estInput) {
        estInput.value = Util.to2(target * hariKerja);
    }
}

/* ================================
   TOTAL OUTPUT
================================ */
function recalcTotalOutput(block) {
    const target = Number(block.querySelector('.input-target')?.value);

    let total = 0;

    block.querySelectorAll('.mesin-row-item').forEach(row => {
        const estInput = row.querySelector('input[name^="est"]');
        if (estInput && estInput.value) {
            total += Number(estInput.value);
        }
    });

    block.querySelector('.total-est')?.replaceChildren(
        document.createTextNode(`Total Output: ${Util.to2(total)} dz`)
    );

    return Util.to2(total);
}

/* ================================
   EVENT LISTENER
================================ */
document.addEventListener('change', async function (e) {

    if (
        e.target.classList.contains('input-start') ||
        e.target.classList.contains('input-stop')
    ) {
        const row = e.target.closest('.mesin-row-item');
        const block = e.target.closest('[data-planning-block]');

        // reset cache karena range bisa berubah
        planningLiburCache.delete(block);

        await recalcRow(row, block);
        recalcTotalOutput(block);
    }

    // kalau target berubah → hitung ulang semua mesin
    if (e.target.classList.contains('input-target')) {
        const block = e.target.closest('[data-planning-block]');

        const rows = block.querySelectorAll('.mesin-row-item');

        for (const row of rows) {
            await recalcRow(row, block);
        }

        recalcTotalOutput(block);
    }

});

/* ===============================
   OVERRIDE MODAL & AUTOFILL
================================ */
window.openAddPlanningModal = function (info = null) {
    const modalEl = document.getElementById('modalAddPlan');
    console.log('modalEl', modalEl);
    if (!modalEl) return;

    let area = null;
    let jarum = null;

    if (info && info.mesin) {
        // info.mesin is like "144N_Area 1"
        const parts = info.mesin.split('_');
        if (parts.length > 1) {
            jarum = parts[0];
            area = parts.slice(1).join('_');
        }
    }

    // Update UI Step 1
    document.getElementById('modal-display-area').textContent = area || '-';
    document.getElementById('modal-display-jarum').textContent = jarum || '-';

    document.getElementById('step-select-order').style.display = 'block';
    document.getElementById('step-form').style.display = 'none';
    document.getElementById('planning-content-container').innerHTML = '';

    // Fetch Orders for dropdown
    const orderSelect = $('#modal-select-order'); // Assuming select2 is used or just standard jQuery
    orderSelect.empty().append('<option value="">-- Pilih Order --</option>');

    // Fix select2 bug in modal
    if ($.fn.select2) {
        orderSelect.select2({
            dropdownParent: $('#modalAddPlan'),
            width: '100%'
        });
    }

    if (area && jarum) {
        // Store the current area/jarum for AJAX
        modalEl._currentArea = area;
        modalEl._currentJarum = jarum;

        // Use a role-agnostic URL if possible, or fallback to aps_base_url
        const targetUrl = (typeof aps_base_url !== 'undefined' ? aps_base_url : `${base_url}aps`) + '/getOrdersForModal';

        $.ajax({
            url: targetUrl,
            type: 'POST',
            data: { area: area, jarum: jarum },
            dataType: 'json',
            success: function (res) {
                console.log('AJAX getOrdersForModal response:', res);
                if (res.status === 'success') {
                    modalEl._id_pln_mc = res.id_pln_mc;

                    if (!res.data || res.data.length === 0) {
                        Swal.fire('Info', 'Tidak ada Order (PDK) yang aktif untuk Area dan Jarum ini.', 'info');
                    } else {
                        res.data.forEach(function (item) {
                            orderSelect.append(new Option(item.model, item.id_detail_pln, false, false));
                        });
                        orderSelect.trigger('change');
                    }
                } else {
                    Swal.fire('Error', 'Gagal mengambil daftar order: ' + res.message, 'error');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                Swal.fire('Error', 'Terjadi kesalahan jaringan atau server saat mengambil data order.', 'error');
            }
        });
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
};

$(document).ready(function () {
    $('#btn-lanjut-planning').on('click', function () {
        const orderId = $('#modal-select-order').val();
        const modalEl = document.getElementById('modalAddPlan');
        if (!orderId) {
            Swal.fire('Warning', 'Pilih Order terlebih dahulu!', 'warning');
            return;
        }

        const id_pln_mc = modalEl._id_pln_mc;
        if (!id_pln_mc) {
            Swal.fire('Error', 'Kebutuhan Area belum dibuat / belum siap!', 'error');
            return;
        }

        // Hide step 1, show step 2
        document.getElementById('step-select-order').style.display = 'none';
        document.getElementById('step-form').style.display = 'block';

        // Load via AJAX
        const url = `${base_url}aps/planningpagev2/${orderId}/${id_pln_mc}?iframe=1`;

        showLoading();
        $('#planning-content-container').html('<div class="text-center my-5"><h4>Loading...</h4></div>');

        $.ajax({
            url: url,
            method: 'GET',
            success: function (html) {
                $('#planning-content-container').html(html);
                hideLoading();
            },
            error: function () {
                $('#planning-content-container').html('<div class="alert alert-danger">Gagal memuat form planning.</div>');
                hideLoading();
            }
        });
    });

    $('#btn-back-order').on('click', function () {
        document.getElementById('step-form').style.display = 'none';
        document.getElementById('step-select-order').style.display = 'block';
        document.getElementById('planning-content-container').innerHTML = '';
    });

    // Intercept form submission to prevent full page redirect
    $(document).on('submit', '#formPlanning', function (e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');

        // Hide the back button in the modal context to prevent navigation
        $('#planning-content-container a:contains("Back")').hide();

        showLoading();
        $.ajax({
            url: url,
            method: 'POST',
            data: form.serialize(),
            success: function (res) {
                // The server redirects, so res is the HTML of the refreshed planning page
                $('#planning-content-container').html(res);
                hideLoading();
                // Refresh calendar events
                if (typeof calendar !== 'undefined') {
                    calendar.refetchEvents();
                }

                // Close modal and show success alert
                const modalEl = document.getElementById('modalAddPlan');
                bootstrap.Modal.getInstance(modalEl)?.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Planning berhasil disimpan',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                hideLoading();
                Swal.fire('Error', 'Terjadi kesalahan saat menyimpan data.', 'error');
            }
        });
    });

    // Also intercept links that act like back buttons
    $(document).on('click', '#planning-content-container a.btn-secondary:contains("Back")', function (e) {
        e.preventDefault();
        // Mimic back button of our modal step instead
        $('#btn-back-order').click();
    });

    // Fullscreen Toggle
    $('#btnApsFullscreen').on('click', function () {
        const boardWrapper = document.querySelector('.card');
        const icon = $(this).find('i');

        if (!document.fullscreenElement) {
            if (boardWrapper.requestFullscreen) {
                boardWrapper.requestFullscreen();
            } else if (boardWrapper.webkitRequestFullscreen) {
                boardWrapper.webkitRequestFullscreen();
            } else if (boardWrapper.msRequestFullscreen) {
                boardWrapper.msRequestFullscreen();
            }
            icon.removeClass('fa-expand').addClass('fa-compress');
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            icon.removeClass('fa-compress').addClass('fa-expand');
        }
    });

    document.addEventListener('fullscreenchange', (event) => {
        const icon = $('#btnApsFullscreen').find('i');
        if (!document.fullscreenElement) {
            icon.removeClass('fa-compress').addClass('fa-expand');
            $('body').removeClass('aps-mode'); // Optional: If we want to hide toolbar, we can toggle this
        } else {
            icon.removeClass('fa-expand').addClass('fa-compress');
            $('body').addClass('aps-mode'); // Enable if we want to hide toolbar & legend during fullscreen
        }
    });
});





