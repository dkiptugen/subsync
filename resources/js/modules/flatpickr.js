// Usage: https://flatpickr.js.org/
import flatpickr from "flatpickr";
flatpickr('input[name="datetimes"], .datetimes', {
    enableTime: true,
    mode: "range",
    dateFormat: "Y-m-d H:i:S", // equivalent to Y/M/DD hh:mm A
    defaultDate: [
        new Date().setMinutes(0, 0, 0), // start of hour
        new Date(Date.now() + 32 * 60 * 60 * 1000) // +32 hours
    ]
});
flatpickr('input[name="datesingle"], .datesingle', {
    enableTime: true,
    dateFormat: "Y-m-d H:i:S",
    defaultDate: new Date(),
    allowInput: true
});

flatpickr('input[name="date"], .date', {
    enableTime: true,
    dateFormat: "Y-m-d",
    defaultDate: new Date(),
    allowInput: true
});
flatpickr('#schedule_date', {
    enableTime: false,
    dateFormat: "Y-m-d",
    allowInput: true
});
flatpickr('.starttime', {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    minuteIncrement: 15
});

flatpickr('.endtime', {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    minuteIncrement: 15
});
const reportInput = document.getElementById('reportRangeInput');
const reportSpan = document.querySelector('#reportrange span');

if (reportInput && reportSpan) {
    const today = new Date();
    const last30 = new Date();
    last30.setDate(today.getDate() - 29);

    const fp = flatpickr(reportInput, {
        mode: "range",
        dateFormat: "F j, Y",
        defaultDate: [last30, today],
        onChange: function(selectedDates) {
            if (selectedDates.length === 2) {
                const [start, end] = selectedDates;
                reportSpan.textContent =
                    `${fp.formatDate(start, "F j, Y")} - ${fp.formatDate(end, "F j, Y")}`;
            }
        }
    });

    // initialize display
    reportSpan.textContent =
        `${fp.formatDate(last30, "F j, Y")} - ${fp.formatDate(today, "F j, Y")}`;
}
