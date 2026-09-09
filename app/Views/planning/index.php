<?= $this->extend('layouts/main') ?>

<?= $this->section('styles') ?>
<style>
    .fc .fc-toolbar-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #344767;
    }

    .fc .fc-button-primary {
        background-color: #5e72e4;
        border-color: #5e72e4;
    }

    .fc .fc-button-primary:hover {
        background-color: #4c5fd7;
        border-color: #4c5fd7;
    }

    .fc .fc-button-primary:disabled {
        background-color: #8392ab;
        border-color: #8392ab;
    }

    .fc-event {
        cursor: pointer;
        border: none;
        padding: 1px 4px;
        font-size: .75rem;
    }

    .fc-daygrid-day.fc-day-today {
        background-color: rgba(94, 114, 228, .08);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Deadline & Planning</h6>
    <form method="get">
        <select name="team_id" class="form-control form-control-sm" onchange="this.form.submit()">
            <option value="">-- Semua Tim --</option>
            <?php foreach ($teams as $team) : ?>
                <option value="<?= $team['id'] ?>" <?= $filters['team_id'] == $team['id'] ? 'selected' : '' ?>><?= esc($team['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div id="planningCalendar"></div>
    </div>
</div>

<div class="d-flex flex-wrap" style="gap: 1rem;">
    <?php foreach ($statuses as $s) : ?>
        <span class="text-xs text-secondary d-flex align-items-center">
            <span class="d-inline-block me-1" style="width:10px;height:10px;border-radius:50%;background-color: <?= esc($s['color']) ?>;"></span>
            <?= esc($s['name']) ?>
        </span>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.19/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('planningCalendar');
        const events = <?= json_encode($events) ?>;

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,dayGridWeek,listMonth',
            },
            events: events,
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },
        });

        calendar.render();
    });
</script>
<?= $this->endSection() ?>