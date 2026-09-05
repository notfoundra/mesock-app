/* =====================================================
   PLANNING MODULE – FULL & SAFE VERSION
===================================================== */
let modalAddPlan;

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('modalAddPlan');
    modalAddPlan = new bootstrap.Modal(modalEl);

    window.openAddPlanningModal = () => modalAddPlan.show();

    modalEl.addEventListener('shown.bs.modal', () => {
        initSelect2(modalEl);
    });
});


const Util = {
    to2(num) {
        num = Number(num);
        return Number.isFinite(num) ? Number(num.toFixed(2)) : 0;
    },
    formatDate(date) {
        const yyyy = date.getFullYear();
        const mm = String(date.getMonth() + 1).padStart(2, '0');
        const dd = String(date.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    },
    hoursBetween(start, end) {
        const diff = end - start;
        return diff > 0 ? diff / (1000 * 60 * 60) : 0;
    }
};

/* ===============================
   MODAL
================================ */
function openAddPlanningModal() {
    modalAddPlan.show();
}


/* ===============================
   SELECT2
================================ */
function initSelect2(scope = document) {
    $(scope)
        .find('.select-order')
        .not('.select2-hidden-accessible')
        .select2({
            dropdownParent: $('#modalAddPlan'),
            width: '100%',
            placeholder: '-- Pilih Order --'
        });
}

/* ===============================
   GLOBAL CHANGE HANDLER
================================ */
document.addEventListener('change', function (e) {
    const header = e.target.closest('.planning-header');
    const block = e.target.closest('.planning-block');

    if (!header) return;

    if (e.target.classList.contains('select-area')) {
        getListPdk(header);
        return;
    }

    if (e.target.classList.contains('select-delivery')) {
        getListInisial(header);
        autoFillDate(block);
        return;
    }

    if (e.target.classList.contains('select-style') && block) {
        getModelData(block);
    }
});

document.addEventListener('change', async e => {

    if (!e.target.matches('input[type="date"][name^="start"], input[type="date"][name^="stop"]'))
        return;

    const row = e.target.closest('.mesin-row-item');
    const block = e.target.closest('.planning-block');
    if (!row || !block) return;

    if (e.target.name?.startsWith('stop')) {
        e.target.dataset.manual = 'true';
    }

    const target = Number(block.querySelector('.input-target')?.value);

    if (!target) {
        recalcTotalOutput(block);
        return;
    }

    // 🔥 hanya hitung row yang berubah
    await recalcRowEst(row, target, block);

    // update total
    recalcTotalOutput(block);

    // label
    updateMachineLabel(block);

    const hasAnyEst = [...block.querySelectorAll('input[name^="est"]')]
        .some(i => Number(i.value) > 0);

    if (!hasAnyEst) {
        await autoBalanceEst(block);
    }
});

document.addEventListener('input', e => {
    if (!e.target.classList.contains('input-target')) return;

    const block = e.target.closest('.planning-block');
    if (!block) return;

    const target = Number(e.target.value);
    if (!target) {
        block.querySelectorAll('input[name^="est"]').forEach(input => {
            if (input.dataset.manual !== 'true') input.value = '';
        });
        recalcTotalOutput(block);
        return;
    }

    block.querySelectorAll('.mesin-row-item').forEach(row => {
        recalcRowEst(row, target);
    });

    recalcTotalOutput(block);
});

document.addEventListener('change', function (e) {

    if (!e.target.name?.startsWith('stop')) return;

    const block = e.target.closest('.planning-block');
    if (!block) return;

    enforceDeliveryDeadline(block);
});

document.addEventListener('input', e => {
    if (!e.target.name?.startsWith('est')) return;
    const block = e.target.closest('.planning-block');
    if (!block) return;

    e.target.dataset.manual = 'true';
    recalcTotalOutput(block);
});

document.addEventListener('focusin', e => {
    if (e.target.name?.startsWith('stop')) {
        e.target.blur();
    }
});


// document.addEventListener('change', function (e) {


// });

$(document).on('select2:select', '.select-order', function () {
    const blockHeader = this.closest('.planning-header');
    getDeliveryOrder(blockHeader);
});

function getLockedStopByDelivery(delivery) {
    if (!delivery) return null;
    const d = new Date(delivery);
    d.setDate(d.getDate() - 3);
    return Util.formatDate(d);
}

function clampStopToDelivery(startVal, stopVal, deliveryVal) {
    // console.log(startVal+stopVal+deliveryVal)
    if (!deliveryVal || !startVal || !stopVal) return stopVal;

    const hardLimit = new Date(deliveryVal);
    hardLimit.setDate(hardLimit.getDate() - 3);

    const stopDate = new Date(stopVal);

    return stopDate > hardLimit
        ? Util.formatDate(hardLimit)
        : stopVal;
}

function setStopDate(input, value, block) {
    const delivery = getDeliveryDate(block);
    if (!delivery) {
        input.value = value;
        return false;
    }

    const hardLimit = new Date(delivery);
    hardLimit.setDate(hardLimit.getDate() - 3);

    const chosen = new Date(value);
    const clamped = chosen > hardLimit;

    input.value = chosen > hardLimit
        ? Util.formatDate(hardLimit)
        : value;

    if (clamped && block) {
        block.dataset.stopClamped = 'true';
    }

    return clamped;
}

function hardLockStopInput(stopInput, block) {
    if (stopInput.dataset.locked === 'true') return;

    const delivery = getDeliveryDate(block);
    if (!delivery) return;

    const hardLimit = new Date(delivery);
    hardLimit.setDate(hardLimit.getDate() - 3);

    const lockValue = Util.formatDate(hardLimit);

    // set awal
    stopInput.value = lockValue;
    stopInput.dataset.locked = 'true';

    const observer = new MutationObserver(() => {
        if (stopInput.value !== lockValue) {
            stopInput.value = lockValue;
            console.warn('[HARD LOCK] stop dipaksa H-3');
        }
    });

    observer.observe(stopInput, {
        attributes: true,
        attributeFilter: ['value']
    });
}

function autoFillDate(block) {
    const delivery = getDeliveryDate(block);
    if (!delivery || !block) return;

    // stop = H-3 delivery
    const stopDate = new Date(delivery);
    stopDate.setDate(stopDate.getDate() - 3);
    const stopVal = Util.formatDate(stopDate);

    // start = besok
    const startDate = new Date();
    startDate.setDate(startDate.getDate() + 1);
    const startVal = Util.formatDate(startDate);

    block.querySelectorAll('.mesin-row-item').forEach(row => {
        const startInput = row.querySelector('input[name^="start"]');
        const stopInput = row.querySelector('input[name^="stop"]');

        if (
            startInput &&
            !startInput.value &&
            startInput.dataset.manual !== 'true'
        ) {
            startInput.value = startVal;
        }

        if (stopInput && !stopInput.value) {
            setStopDate(stopInput, stopVal, block);
        }
    });

    // setelah tanggal terisi, pastikan MC terakhir jadi parsial sesuai sisa
    autoBalanceMachineByDate(block);
}

/* ===============================
   API – ORDER
================================ */
function getListPdk(blockHeader) {
    const area = blockHeader.querySelector('.select-area')?.value;
    const orderSelect = blockHeader.querySelector('.select-order');
    if (!area || !orderSelect) return;

    fetch(`${base_url}api/getListOrder?area=${area}`)
        .then(r => r.json())
        .then(r => Array.isArray(r.result) && renderOrderOptions(orderSelect, r.result, blockHeader))
        .catch(err => console.error(err));
}

function getModelDataByIdaps(block, idaps) {

    if (!idaps) return;

    fetch(`${base_url}api/getModelData?id=${idaps}`)
        .then(r => r.json())
        .then(r => r.result && renderStyleData(block, r.result))
        .catch(console.error);
}

function renderOrderOptions(selectEl, list, blockHeader) {

    selectEl.innerHTML = `<option value="">-- Pilih Model / Jarum --</option>`;

    list.forEach(row => {
        selectEl.insertAdjacentHTML(
            'beforeend',
            `<option 
                    value="${row.id_detail_pln}"
                    data-jarum="${row.jarum}"
                >
                    ${row.model} - ${row.jarum}
                </option>`
        );
    });

    // set jarum saat user pilih option
    selectEl.onchange = function () {
        const jarum = this.selectedOptions[0]?.dataset.jarum || '';
        const jarumInput = blockHeader.querySelector('.jarum');
        if (jarumInput) jarumInput.value = jarum;
    };

    $(selectEl).trigger('change');
}


/* ===============================
   API – DELIVERY
================================ */
function getDeliveryOrder(header) {
    if (!header) return;

    const orderId = header.querySelector('.select-order')?.value;
    const deliverySelect = header.querySelector('.select-delivery');
    const area = header.querySelector('.select-area')?.value;

    if (!orderId || !deliverySelect) return;

    fetch(`${base_url}api/getDelivOrder?id=${orderId}&area=${area}`)
        .then(r => r.json())
        .then(r => Array.isArray(r.result) && renderDeliveryOptions(deliverySelect, r.result));
}

function renderDeliveryOptions(selectEl, list) {
    selectEl.innerHTML = `<option value="">-- Pilih Delivery --</option>`;
    list.forEach(row => {
        selectEl.insertAdjacentHTML(
            'beforeend',
            `<option value="${row.delivery}">${row.delivery}</option>`
        );
    });
}


function buildPlanningByStyle(styles) {

    const container = document.getElementById('planningContainer');

    // simpan template
    const template = container.querySelector('.planning-block');

    // hapus semua planning lama
    container.innerHTML = '';

    styles.forEach((row, i) => {

        const clone = template.cloneNode(true);

        // ===== TITLE =====
        clone.querySelector('.planning-title').innerText = `Planning #${i + 1}`;

        // ===== INISIAL STYLE =====
        clone.querySelector('input[name="inStyle[]"]').value =
            `${row.inisial} || ${row.size}`;
        // ===== STYLE =====
        clone.querySelector('input[name="style[]"]').value =
            `${row.size}`;

        // simpan idaps (penting untuk API model)
        clone.dataset.idaps = row.idapsperstyle;

        // reset numeric input
        clone.querySelectorAll(
            'input[type="number"], input[type="date"]'
        ).forEach(i => i.value = '');

        // reset mesin
        const mesinContainer = clone.querySelector('.mesinContainer');
        const firstRow = mesinContainer.querySelector('.mesin-row-item');
        firstRow.classList.add('mesin-row-item');
        mesinContainer.innerHTML = '';
        mesinContainer.appendChild(firstRow);
        firstRow.querySelectorAll('input').forEach(i => i.value = '');

        container.appendChild(clone);

        // 🔥 load data model per style
        getModelDataByIdaps(clone, row.idapsperstyle);
    });
}


/* ===============================
   API – STYLE
================================ */
function getListInisial(header) {

    const orderId = header.querySelector('.select-order')?.value;
    const delivery = header.querySelector('.select-delivery')?.value;
    const area = header.querySelector('.select-area')?.value;

    if (!orderId || !delivery) return;

    fetch(`${base_url}api/getListInisial?id=${orderId}&delivery=${delivery}&area=${area}`)
        .then(r => r.json())
        .then(r => {
            if (!Array.isArray(r.result)) return;
            buildPlanningByStyle(r.result);

            document.querySelectorAll('.planning-block').forEach(block => {
                autoFillDate(block);
                // recalculate(block);
            });
        })
        .catch(console.error);
}

function renderStyleOptions(selectEl, list) {
    selectEl.innerHTML = `<option value="">-- Pilih Style --</option>`;
    list.forEach(row => {
        selectEl.insertAdjacentHTML(
            'beforeend',
            `<option value="${row.idapsperstyle}">
                ${row.inisial} || ${row.size}
            </option>`
        );
    });
}

/* ===============================
   API – MODEL DATA
================================ */
function getModelData(block) {
    const idaps = block.querySelector('.select-style')?.value;
    if (!idaps) return;

    fetch(`${base_url}api/getModelData?id=${idaps}`)
        .then(r => r.json())
        .then(r => r.result && renderStyleData(block, r.result))
        .catch(err => console.error(err));

    // console.log(r.result); 
}

function getDeliveryDate(block) {
    if (!block) return null;

    const modal = block.closest('.modal');
    if (!modal) return null;

    return modal.querySelector('.select-delivery')?.value ?? null;
}

function renderStyleData(block, data) {
    block.querySelector('.input-target').value = Util.to2(3600 / data.smv);
    block.querySelector('.input-qty').value = Util.to2(data.qty / 24);
    block.querySelector('.input-sisa').value = Util.to2(data.sisa / 24);
    block.querySelector('.input-planned').value = Util.to2(data.planned_qty);

    recalculate(block);
    autoBalanceMachineByDate(block);
    // enforceDeliveryDeadline(block);
    // // console.log('renderStyleData');
    updateMachineLabel(block);
}

/* ===============================
   MACHINE CALCULATION
================================ */
function recalculate(block) {
    const target = Number(block.querySelector('.input-target')?.value);
    const sisa = Number(block.querySelector('.input-sisa')?.value);
    const start = block.querySelector('input[name^="start"]')?.value;
    const delivery = getDeliveryDate(block);

    // ❗ HARD GUARD
    if (!target || !sisa || !start || !delivery) {
        return;
    }

    const startDate = new Date(start);
    const deliveryDate = new Date(delivery);

    const diffDays = (deliveryDate - startDate) / 86400000;
    // ❗ kalau hasil invalid → STOP
    if (!Number.isFinite(diffDays) || diffDays <= 3) {
        return;
    }

    const MIN_MC = 1;
    const MAX_MC = 15;
    const MIN_DAYS = 1;

    const maxAllowedDays = Math.max(MIN_DAYS, diffDays - 3);

    let leadDays = maxAllowedDays;

    let needMC = Math.ceil(sisa / (target * leadDays));
    needMC = Math.max(MIN_MC, Math.min(needMC, MAX_MC));

    // recalibrate ulang leadDays
    leadDays = sisa / (target * needMC);
    leadDays = Math.max(MIN_DAYS, Math.min(leadDays, maxAllowedDays));

    const outputPerMC = Util.to2(sisa / needMC);

    applyMachineRowsBalanced(block, {
        needMC,
        start,
        totalDays: leadDays,
        outputPerMC,
        sisa
    });
    // return {
    //     needMC,
    //     start,
    //     totalDays: leadDays,
    //     outputPerMC,
    //     sisa
    // };
}

function updateRowDates(block) {
    const start = block.querySelector('input[name^="start"]')?.value;
    if (!start) return;

    block.querySelectorAll('.mesin-row-item').forEach(row => {
        const stopInput = row.querySelector('input[name^="stop"]');
        if (!stopInput?.dataset.manual) {
            stopInput.value = block.dataset.stopLock;
        }
    });
}

function cloneMachineRow(row) {
    const clone = row.cloneNode(true); // 🔥 DEEP CLONE

    // reset field tertentu saja
    clone.querySelectorAll('input').forEach(input => {
        if (input.name?.startsWith('est')) {
            input.value = '';
        }
        // start & stop ikut copy (preserve)
    });

    return clone;
}

function syncMachineRows(block, needMC) {
    const container = block.querySelector('.mesin-row-container');
    if (!container) return;

    let rows = container.querySelectorAll('.mesin-row-item');
    console.log('MC:', rows.length, '→ need:', needMC);
    const currentMC = rows.length;

    // ➕ TAMBAH MC
    if (needMC > currentMC) {
        const lastRow = rows[rows.length - 1];

        for (let i = currentMC; i < needMC; i++) {
            const newRow = cloneMachineRow(lastRow);
            container.appendChild(newRow);
        }
    }

    // ➖ KURANGI MC
    if (needMC < currentMC) {
        for (let i = currentMC; i > needMC; i--) {
            container.lastElementChild?.remove();
        }
    }
}

function updateMachineLabel(block) {
    const count = block.querySelectorAll('.mesin-row-item').length;
    block.querySelector('.rec-machines')?.replaceChildren(
        document.createTextNode(`Mesin (Balanced: ${count} MC)`)
    );
}

function getManualEstTotal(block) {
    let total = 0;

    block.querySelectorAll('input[name^="est"]').forEach(input => {
        if (input.dataset.manual === 'true') {
            total += Number(input.value) || 0;
        }
    });

    return total;
}

function distributeEst(block, sisa) {
    const rows = [...block.querySelectorAll('.mesin-row-item')];
    const manualTotal = getManualEstTotal(block);
    const remain = Math.max(0, sisa - manualTotal);

    const autoRows = rows.filter(r =>
        !r.querySelector('input[name^="est"]')?.dataset.manual
    );

    if (!autoRows.length) return;

    const perRow = Util.to2(remain / autoRows.length);

    autoRows.forEach((row, i) => {
        const input = row.querySelector('input[name^="est"]');
        if (!input) return;

        input.value =
            i === autoRows.length - 1
                ? Util.to2(remain - perRow * (autoRows.length - 1))
                : perRow;
    });
}

function updateTotalEst(block) {
    let total = 0;

    block.querySelectorAll('input[name^="est"]').forEach(i => {
        const v = Number(i.value);
        if (!isNaN(v)) total += v;
    });

    total = Util.to2(total);

    block.querySelector('.total-est')?.replaceChildren(
        document.createTextNode(`Total Estimated Output: ${total}`)
    );

    return total;
}

function autoBalanceEst(block) {
    const sisa = Number(block.querySelector('.input-sisa')?.value);
    if (!sisa) return;

    const rows = [...block.querySelectorAll('.mesin-row-item')];
    if (!rows.length) return;

    let totalManual = 0;
    let autoInputs = [];

    rows.forEach(row => {
        const est = row.querySelector('input[name^="est"]');
        if (!est) return;

        if (est.dataset.manual === 'true') {
            totalManual += Number(est.value || 0);
        } else {
            autoInputs.push(est);
        }
    });

    const remaining = Util.to2(sisa - totalManual);
    if (remaining < 0) return;

    if (!autoInputs.length) return;

    const perRow = Util.to2(remaining / autoInputs.length);

    autoInputs.forEach(i => i.value = perRow);

    // updateTotalEst(block);
}


/* ===============================
MACHINE ROW MANAGER – BALANCED
================================ */
function applyMachineRowsBalanced(block, cfg) {
    const {
        needMC,
        start,
        totalDays,
        outputPerMC,
        sisa
    } = cfg;


    const container = block.querySelector('.mesinContainer');
    if (!container) return;


    let rows = [...container.querySelectorAll('.mesin-row-item')];
    if (!rows.length) return;


    const base = rows[0];

    /* ===== INFO ===== */
    block.querySelector('.rec-machines')?.replaceChildren(
        document.createTextNode(`Mesin (Balanced: ${needMC} MC)`)
    );


    block.querySelector('.total-est')?.replaceChildren(
        document.createTextNode(`Target Produksi: ${sisa} dz`)
    );

    /* ===== NORMALIZE ROW COUNT ===== */
    while (rows.length > needMC) {
        container.removeChild(container.lastElementChild);
        rows.pop();
    }


    while (rows.length < needMC) {
        const clone = base.cloneNode(true);
        clone.classList.add('mesin-row-item');
        clone.querySelectorAll('input').forEach(i => {
            if (!i.value) i.value = '';
        });
        container.appendChild(clone);
        rows.push(clone);
    }


    /* ===== CALCULATE STOP TIME (ALL SAME) ===== */
    const header = block.closest('.planning-header');
    const startDate = new Date(start);
    const stopDate = new Date(startDate.getTime() + totalDays * 24 * 60 * 60 * 1000);
    const stopVal = Util.formatDate(stopDate);

    /* ===== APPLY TO ALL MACHINES ===== */
    rows.forEach(row => {
        row.querySelector('input[name^="start"]').value = start;
        row.querySelector('input[name^="stop"]').value = stopVal;
        row.querySelector('input[name^="est"]').value = outputPerMC;
    });

    // initial total sync
    recalcTotalOutput(block);
}

function recalcRowEst(row, target) {

    // 🔒 kalau user isi manual, JANGAN SENTUH
    const estInput = row.querySelector('input[name^="est"]');
    if (!estInput || estInput.dataset.manual === 'true') return;
    if (!target || target <= 0) return;
    const startVal = row.querySelector('input[name^="start"]')?.value;
    const stopVal = row.querySelector('input[name^="stop"]')?.value;

    if (!startVal || !stopVal || !target || !estInput) return;

    const hours =
        (new Date(stopVal) - new Date(startVal)) / 3600000;

    if (hours <= 0) return;

    const output = (target / 24) * hours;
    estInput.value = Util.to2(output);
}

function getRowOutput(row, target) {
    const estInput = row.querySelector('input[name^="est"]');
    if (estInput?.dataset.manual === 'true') {
        return Util.to2(Number(estInput.value) || 0);
    }

    const startVal = row.querySelector('input[name^="start"]')?.value;
    const stopVal = row.querySelector('input[name^="stop"]')?.value;

    if (startVal && stopVal && target) {
        const hours =
            (new Date(stopVal) - new Date(startVal)) / 3600000;

        if (hours > 0) {
            return Util.to2((target / 24) * hours);
        }
    }

    return Util.to2(Number(estInput?.value) || 0);
}

function recalcTotalOutput(block) {
    const target = Number(block.querySelector('.input-target')?.value);

    let total = 0;


    block.querySelectorAll('.mesin-row-item').forEach(row => {
        const startVal = row.querySelector('input[name^="start"]')?.value;
        const stopVal = row.querySelector('input[name^="stop"]')?.value;
        const estInput = row.querySelector('input[name^="est"]');
        let output = 0;

        // PRIORITAS 1: hitung dari start-stop
        if (startVal && stopVal && target) {
            const hours =
                (new Date(stopVal) - new Date(startVal)) / (1000 * 60 * 60);

            if (hours > 0) {
                output = (target / 24) * hours;
            }
        }
        // FALLBACK: pakai est manual
        if (!output && estInput?.value) {
            output = Number(estInput.value);
        }

        total += Util.to2(output);
    });

    // tampilkan ke UI
    block.querySelector('.total-est')?.replaceChildren(
        document.createTextNode(`Total Output: ${Util.to2(total)} dz`)
    );

    return Util.to2(total);
}

/* ===============================
   MC ADD / DELETE
================================ */

document.addEventListener('click', e => {
    if (e.target.classList.contains('btn-addRow')) {
        const row = e.target.closest('.mesin-row');
        const container = row.closest('.mesinContainer');
        const clone = row.cloneNode(true);
        clone.classList.add('mesin-row-item');
        clone.querySelectorAll('input').forEach(i => i.value = '');
        container.appendChild(clone);

    }
    if (e.target.classList.contains('btn-deleteRow')) {
        const row = e.target.closest('.mesin-row');
        const container = row?.closest('.mesinContainer');
        if (!row || !container) return;

        const rows = container.querySelectorAll('.mesin-row');
        if (rows.length > 1) row.remove();
    }
});

function reindexPlanning() {
    document.querySelectorAll('.planning-block').forEach((el, i) => {
        el.querySelector('.planning-title').innerText = `Planning #${i + 1}`;
    });
}
document.addEventListener('click', function (e) {

    /* =====================
       TAMBAH PLANNING
    ====================== */
    if (e.target.classList.contains('btn-addPlan')) {

        const container = document.getElementById('planningContainer');
        const blocks = container.querySelectorAll('.planning-block');

        const source = blocks[0];

        // 🔥 destroy select2 di source
        $(source).find('.select-order.select2-hidden-accessible').select2('destroy');

        const clone = source.cloneNode(true);
        const index = blocks.length;

        // reset input & select
        clone.querySelectorAll('input').forEach(i => i.value = '');
        clone.querySelectorAll('select').forEach(s => {
            s.value = '';
            s.removeAttribute('data-select2-id');
        });

        clone.querySelector('.planning-title').innerText = `Planning #${index + 1}`;

        // reset mesin
        const mesinContainer = clone.querySelector('.mesinContainer');
        const firstRow = mesinContainer.querySelector('.mesin-row-item');
        firstRow.classList.add('mesin-row-item');
        mesinContainer.innerHTML = '';
        mesinContainer.appendChild(firstRow);
        firstRow.querySelectorAll('input').forEach(i => i.value = '');

        container.appendChild(clone);

        // 🔥 init select2 ulang SEMUA
        initSelect2(container);
    }

    /* =====================
       DELETE PLANNING
    ====================== */
    if (e.target.classList.contains('btn-removePlanning')) {

        const block = e.target.closest('.planning-block');
        const total = document.querySelectorAll('.planning-block').length;

        if (total <= 1) {
            alert('Minimal harus ada 1 planning');
            return;
        }

        block.remove();
        reindexPlanning();
    }

});

// ===============================
// AUTO HITUNG EST (PAKSA JALAN)
// ===============================
// document.addEventListener('input', function (e) {

//     if (!e.target.matches('input[type="date"][name^="start"], input[type="date"][name^="stop"]')) {
//         return;
//     }

//     // console.log('🔥 INPUT DATE DETECTED', e.target.name, e.target.value);

//     const row = e.target.closest('.mesin-row-item');
//     if (!row) {
//         console.warn('row not found');
//         return;
//     }

//     const block = e.target.closest('.planning-block');
//     if (!block) {
//         console.warn('planning block not found');
//         return;
//     }

//     if (e.target.name?.startsWith('stop')) {
//         e.target.dataset.manual = 'true';
//     }

//     if (!e.target.name?.startsWith('est')) return;

//     // const block = e.target.closest('.planning-block');
//     if (!block) return;

//     e.target.dataset.manual = 'true';

//     recalcTotalOutput(block);
// });

function autoAddMachineIfNeeded(block) {
    const sisa = Number(block.querySelector('.input-sisa')?.value);
    if (isNaN(sisa) || sisa <= 0) return;

    const container = block.querySelector('.mesinContainer');
    const rows = [...container.querySelectorAll('.mesin-row-item')];

    const MAX_MC = 15;
    let total = recalcTotalOutput(block);

    while (total < sisa && rows.length < MAX_MC) {

        const base = rows[0];
        const clone = base.cloneNode(true);
        clone.classList.add('mesin-row-item');

        // reset input penting
        clone.querySelector('input[name^="no_mc"]').value = '';
        clone.querySelector('input[name^="est"]').value = '';

        // copy tanggal dari row pertama
        clone.querySelector('input[name^="start"]').value =
            base.querySelector('input[name^="start"]').value;

        clone.querySelector('input[name^="stop"]').value =
            base.querySelector('input[name^="stop"]').value;

        container.appendChild(clone);
        rows.push(clone);

        // hitung est MC baru
        const target = Number(block.querySelector('.input-target')?.value);
        recalcRowEst(clone, target);

        // hitung ulang total
        total = recalcTotalOutput(block);
    }
}

function __deprecated_getTotalOutput(block) {
    const target = Number(block.querySelector('.input-target')?.value);
    if (isNaN(target)) return 0;

    let total = 0;

    block.querySelectorAll('.mesin-row-item').forEach(row => {
        const startVal = row.querySelector('input[name^="start"]')?.value;
        const stopVal = row.querySelector('input[name^="stop"]')?.value;
        const estInput = row.querySelector('input[name^="est"]');

        let output = 0;

        if (startVal && stopVal) {
            const hours =
                (new Date(stopVal) - new Date(startVal)) / (1000 * 60 * 60);
            if (hours > 0) {
                output = (target / 24) * hours;
            }
        }

        if (!output && estInput?.value) {
            output = Number(estInput.value);
        }

        total += Util.to2(output);
    });

    return Util.to2(total);
}

function autoBalanceMachineByDate(block) {
    const sisa = Number(block.querySelector('.input-sisa')?.value);
    const target = Number(block.querySelector('.input-target')?.value);
    if (isNaN(sisa) || sisa <= 0 || isNaN(target) || target <= 0) return;

    const container = block.querySelector('.mesinContainer');
    let rows = [...container.querySelectorAll('.mesin-row-item')];

    const MAX_MC = 15;

    // hapus badge lama
    clearShortfallBadge(block);

    // reset flag remainder lama
    rows.forEach(r => {
        delete r.dataset.remainder;
        delete r.dataset.autoAdded;
        r.classList.remove('bg-warning-subtle');
    });
    delete block.dataset.stopClamped;

    if (!rows.length) return;

    const base = rows[0];
    const baseStart = base.querySelector('input[name^="start"]')?.value;
    const baseStop = base.querySelector('input[name^="stop"]')?.value;
    if (!baseStart || !baseStop) return;

    // total output row existing (manual est tetap dihitung manual)
    let total = 0;
    rows.forEach(row => {
        total += getRowOutput(row, target);
    });

    let remaining = Util.to2(sisa - total);

    const fullHours =
        (new Date(baseStop) - new Date(baseStart)) / 3600000;
    const fullOutput = fullHours > 0
        ? Util.to2((target / 24) * fullHours)
        : 0;

    // === TAMBAH MC BARU SESUAI KEBUTUHAN, LALU BUAT PARSIAL ===
    const maxAdd = Math.max(0, MAX_MC - rows.length);
    const needExtra = fullOutput > 0
        ? Math.min(maxAdd, Math.ceil(remaining / fullOutput))
        : 0;

    for (let i = 0; i < needExtra; i++) {
        if (remaining <= 0) break;
        const clone = base.cloneNode(true);
        clone.classList.add('mesin-row-item');
        clone.dataset.autoAdded = 'true';
        clone.dataset.remainder = 'true';

        clone.querySelector('input[name^="no_mc"]').value = '';
        clone.querySelector('input[name^="est"]').value = '';

        const startInput = clone.querySelector('input[name^="start"]');
        const stopInput = clone.querySelector('input[name^="stop"]');

        if (startInput) startInput.value = baseStart;

        const desiredOutput = Math.min(remaining, fullOutput);
        const needDays = Math.max(1, desiredOutput / target);
        const stopDate = new Date(new Date(baseStart).getTime() + needDays * 24 * 60 * 60 * 1000);
        const stopVal = Util.formatDate(stopDate);

        if (stopInput) {
            setStopDate(stopInput, stopVal, block);
        }

        recalcRowEst(clone, target);

        container.appendChild(clone);
        rows.push(clone);

        const actualOutput = getRowOutput(clone, target);
        remaining = Util.to2(remaining - actualOutput);
    }

    if (isSplitStopEnabled(block)) {
        distributeStopsBySisa(block);
    }

    const totalNow = recalcTotalOutput(block);
    updateMachineLabel(block);

    // === WARNING JIKA MASIH KURANG KARENA H-3 CLAMP ===
    if (totalNow < sisa && block.dataset.stopClamped === 'true') {
        showShortfallBadge(block);
    }
}

// function showMcWarning(block) {
//     if (block.querySelector('.mc-warning')) return;

//     const badge = document.createElement('div');
//     badge.className = 'mc-warning mt-2 text-warning fw-semibold';
//     badge.innerHTML = '⚠️ Perlu MC tambahan untuk memenuhi target';

//     const header = block.querySelector('.mesin-header');
//     header?.appendChild(badge);
// }

function adjustLastMachineStopDate(block) {

    const sisa = Number(block.querySelector('.input-sisa')?.value);
    const target = Number(block.querySelector('.input-target')?.value);

    if (!sisa || !target) return;

    const rows = [...block.querySelectorAll('.mesin-row-item')];
    if (!rows.length) return;

    // === HITUNG OUTPUT SEMUA MC KECUALI TERAKHIR ===
    let produced = 0;

    rows.slice(0, -1).forEach(row => {
        const startVal = row.querySelector('input[name^="start"]')?.value;
        const stopVal = row.querySelector('input[name^="stop"]')?.value;
        if (!startVal || !stopVal) return;

        const hours =
            (new Date(stopVal) - new Date(startVal)) / (1000 * 60 * 60);

        if (hours > 0) {
            produced += (target / 24) * hours;
        }
    });

    const remaining = Util.to2(sisa - produced);
    if (remaining <= 0) return;

    // === MC TERAKHIR ===
    const lastRow = rows[rows.length - 1];
    const startVal = lastRow.querySelector('input[name^="start"]')?.value;
    const stopInput = lastRow.querySelector('input[name^="stop"]');

    if (!startVal || !stopInput) return;

    // durasi hari yg dibutuhkan
    const needDays = Math.max(1, remaining / target);

    const startDate = new Date(startVal);
    const stopDate = new Date(startDate.getTime() + needDays * 24 * 60 * 60 * 1000);
    const stopVal = Util.formatDate(stopDate);

    setStopDate(stopInput, stopVal, block);

    // hitung ulang est biar konsisten dgn stop baru
    recalcRowEst(lastRow, target);

    // biar kelihatan ini MC parsial
    lastRow.classList.add('bg-warning-subtle');
}

function isSplitStopEnabled(block) {
    return block?.querySelector('.toggle-split-stop')?.checked === true;
}

function distributeStopsBySisa(block) {
    const sisa = Number(block.querySelector('.input-sisa')?.value);
    const target = Number(block.querySelector('.input-target')?.value);
    if (!sisa || !target) return;

    const rows = [...block.querySelectorAll('.mesin-row-item')];
    if (!rows.length) return;

    const baseStart = rows[0].querySelector('input[name^="start"]')?.value;
    if (!baseStart) return;

    // hitung sisa setelah manual est
    let totalManual = 0;
    const autoRows = [];

    rows.forEach(row => {
        const estInput = row.querySelector('input[name^="est"]');
        if (!estInput) return;

        if (estInput.dataset.manual === 'true') {
            const v = Number(estInput.value);
            if (!isNaN(v)) totalManual += v;
        } else {
            autoRows.push(row);
        }
    });

    let remaining = Util.to2(sisa - totalManual);
    if (remaining < 0) remaining = 0;

    const perAuto = autoRows.length
        ? Util.to2(remaining / autoRows.length)
        : 0;

    rows.forEach((row, i) => {
        const startInput = row.querySelector('input[name^="start"]');
        const stopInput = row.querySelector('input[name^="stop"]');
        const estInput = row.querySelector('input[name^="est"]');
        if (!startInput || !stopInput) return;

        let output = 0;
        const isManual = estInput?.dataset.manual === 'true';

        if (isManual) {
            output = Number(estInput?.value) || 0;
        } else {
            if (!autoRows.length) {
                output = 0;
            } else if (autoRows[autoRows.length - 1] === row) {
                output = Util.to2(remaining);
            } else {
                output = perAuto;
            }
            remaining = Util.to2(remaining - output);
        }

        const needDays = Math.max(1, output / target);
        const startDate = new Date(baseStart);
        const stopDate = new Date(startDate.getTime() + needDays * 24 * 60 * 60 * 1000);
        const stopVal = Util.formatDate(stopDate);

        startInput.value = baseStart;
        setStopDate(stopInput, stopVal, block);
        if (!isManual) {
            recalcRowEst(row, target);
        }
    });
}

document.addEventListener('change', e => {
    if (!e.target.classList.contains('toggle-split-stop')) return;
    const block = e.target.closest('.planning-block');
    if (!block) return;

    autoBalanceMachineByDate(block);
});

function showShortfallBadge(block) {
    if (block.querySelector('.mc-warning')) return;

    const badge = document.createElement('div');
    badge.className = 'mc-warning mt-2 text-warning fw-semibold';
    badge.textContent = '⚠️ Output kurang karena stop ter-clamp H-3 delivery';

    const header = block.querySelector('.mesin-header');
    header?.appendChild(badge);
}

function clearShortfallBadge(block) {
    block.querySelector('.mc-warning')?.remove();
}

function showOverDeliveryBadge(block, msg = '❗ OVER DELIVERY (melewati H-3)') {
    if (block.querySelector('.over-delivery')) return;

    const badge = document.createElement('div');
    badge.className = 'over-delivery mt-2 text-danger fw-bold';
    badge.textContent = msg;

    block.querySelector('.mesin-header')?.appendChild(badge);
}

function clearOverDeliveryBadge(block) {
    block.querySelector('.over-delivery')?.remove();
}

function enforceDeliveryDeadline(block) {
    const delivery = getDeliveryDate(block);
    if (!delivery) return;

    block.querySelectorAll('input[name^="stop"]').forEach(input => {
        input.value = clampStopToDelivery(
            block.querySelector('input[name^="start"]')?.value,
            input.value,
            delivery
        );
    });
}


