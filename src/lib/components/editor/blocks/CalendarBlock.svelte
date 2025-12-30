<script>
    import { onMount } from 'svelte';
    import { NodeViewWrapper } from 'svelte-tiptap';
    import { Calendar } from '@fullcalendar/core';
    import dayGridPlugin from '@fullcalendar/daygrid';
    import timeGridPlugin from '@fullcalendar/timegrid';
    import interactionPlugin from '@fullcalendar/interaction';
    import EventModal from '../modals/EventModal.svelte';

    export let node;
    export let updateAttributes;
    // export let editor; // Unused
    // export let getPos; // Unused

    let calendarEl;
    let calendar;
    let isModalOpen = false;
    let currentEvent = {};

    onMount(() => {
        initCalendar();

        return () => {
            if (calendar) calendar.destroy();
        };
    });

    function initCalendar() {
        if (!calendarEl) return;

        calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            events: fetchEvents,
            select: handleDateSelect,
            eventClick: handleEventClick,
            eventDrop: handleEventDrop, // Drag and drop updates
            eventResize: handleEventResize
        });

        calendar.render();
    }

    async function fetchEvents(info, successCallback, failureCallback) {
        try {
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr,
                showAll: node.attrs.showAllCalendars
            });

            const res = await fetch(`/api/calendar/events?${params}`);
            const data = await res.json();

            successCallback(data);
        } catch (error) {
            console.error('Error fetching events', error);
            failureCallback(error);
        }
    }

    function handleDateSelect(selectInfo) {
        currentEvent = {
            start: selectInfo.startStr,
            end: selectInfo.endStr,
            title: '',
            is_visible: true,
            color: '#3788d8'
        };
        isModalOpen = true;
    }

    function handleEventClick(clickInfo) {
        const props = clickInfo.event.extendedProps;
        currentEvent = {
            id: clickInfo.event.id,
            title: clickInfo.event.title,
            start: clickInfo.event.startStr, // ISO string
            end: clickInfo.event.endStr, // ISO string
            url: clickInfo.event.url, // FullCalendar handles URL nav by default, we prevent it?
            description: props.description,
            image: props.image,
            is_visible: props.is_visible,
            color: clickInfo.event.backgroundColor
        };

        clickInfo.jsEvent.preventDefault(); // Don't navigate to URL
        isModalOpen = true;
    }

    async function handleEventDrop(info) {
        await updateEventPosition(info.event);
    }

    async function handleEventResize(info) {
        await updateEventPosition(info.event);
    }

    async function updateEventPosition(eventObj) {
         try {
            const payload = {
                start_date: eventObj.startStr, // Use standard property names expected by backend
                end_date: eventObj.endStr
            };

            await fetch(`/api/calendar/events/${eventObj.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
        } catch (e) {
            console.error('Update failed', e);
            info.revert();
        }
    }

    async function saveEvent(eventData) {
        const isNew = !eventData.id;
        const url = isNew ? '/api/calendar/events' : `/api/calendar/events/${eventData.id}`;
        const method = isNew ? 'POST' : 'PUT';

        // Prepare payload mapping back to backend fields
        const payload = {
            title: eventData.title,
            start_date: eventData.start,
            end_date: eventData.end,
            url: eventData.url,
            image: eventData.image,
            description: eventData.description,
            color: eventData.color,
            is_visible: eventData.is_visible
        };

        try {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                isModalOpen = false;
                calendar.refetchEvents();
            } else {
                alert('Error saving event');
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function deleteEvent(eventData) {
        if (!eventData.id) return;

        try {
            const res = await fetch(`/api/calendar/events/${eventData.id}`, {
                method: 'DELETE'
            });

            if (res.ok) {
                isModalOpen = false;
                calendar.getEventById(eventData.id).remove();
            }
        } catch (err) {
            console.error(err);
        }
    }

    function toggleShowAll() {
        updateAttributes({ showAllCalendars: !node.attrs.showAllCalendars });
        setTimeout(() => calendar.refetchEvents(), 100);
    }
</script>

<NodeViewWrapper class="calendar-wrapper relative my-4 p-4 border rounded-lg bg-white shadow dark:bg-gray-800 dark:border-gray-700">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold dark:text-white">Events Calendar</h3>
        <div class="flex items-center gap-2">
            <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" checked={node.attrs.showAllCalendars} onchange={toggleShowAll} class="sr-only peer">
                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">Show All Calendars</span>
            </label>
        </div>
    </div>

    <div bind:this={calendarEl} class="fullcalendar-container min-h-[500px]"></div>

    <EventModal
        isOpen={isModalOpen}
        event={currentEvent}
        onclose={() => isModalOpen = false}
        onsave={saveEvent}
        ondelete={deleteEvent}
    />
</NodeViewWrapper>

<style>
    /* FullCalendar overrides for Tailwind/DaisyUI compatibility if needed */
    :global(.fc) {
        font-family: inherit;
    }
    :global(.fc-toolbar-title) {
        font-size: 1.25rem !important;
    }
    :global(.fc-button-primary) {
        background-color: #3b82f6 !important; /* Tailwind blue-500 */
        border-color: #3b82f6 !important;
    }
    :global(.fc-button-active) {
        background-color: #1d4ed8 !important; /* Tailwind blue-700 */
         border-color: #1d4ed8 !important;
    }
    :global(.fc-daygrid-day-number), :global(.fc-col-header-cell-cushion) {
        color: inherit !important;
        text-decoration: none !important;
    }
    :global(.dark .fc-theme-standard td), :global(.dark .fc-theme-standard th) {
        border-color: #374151; /* gray-700 */
    }
    :global(.dark .fc-daygrid-day-number), :global(.dark .fc-col-header-cell-cushion) {
        color: #e5e7eb !important; /* gray-200 */
    }
</style>
