<?php

namespace app\controllers;

use Flight;
use app\models\CalendarEvent;

class CalendarController
{
    public function getEvents()
    {
        $start = Flight::request()->query->start;
        $end = Flight::request()->query->end;
        $showAll = Flight::request()->query->showAll;

        $model = new CalendarEvent();

        // Start building query
        if ($showAll === 'true' || $showAll === true) {
            $events = $model->findAll();
        } else {
            // Default behavior: Show all events?
            // Since we removed widgetId, there is no scoping filter anymore unless we want page-level scoping?
            // The user said "share events between different pages", which implies global events by default.
            // If "showAll" is false vs true, what's the difference without widgetId?
            // Maybe "showAll" is now redundant or it scopes by something else?
            // I'll default to returning all events for now, effectively ignoring showAll or treating everything as shown.
            $events = $model->findAll();
        }

        // Apply date filtering in PHP since Flight's simple ActiveRecord might not support >= complex queries easily?
        // Wait, typical AR allows where clauses.
        // But for safety and speed in this simple setup, let's filter the array first or trust the DB if we can.
        // Flight AR uses RedBeanPHP style or simple wraper? The User model usage suggested simple `eq`.
        // Let's filter in PHP for now to be safe with the start/end ISO strings.

        $results = [];
        foreach ($events as $event) {
            // Filter by date if provided
            if ($start && $event->end_date < $start)
                continue;
            if ($end && $event->start_date > $end)
                continue;

            $results[] = [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'url' => $event->url,
                'extendedProps' => [
                    'description' => $event->description,
                    'image' => $event->image,
                    'is_visible' => $event->is_visible,
                    'link_new_window' => false // Todo: add to DB if needed
                ],
                'backgroundColor' => $event->color,
                'borderColor' => $event->color
            ];
        }

        Flight::json($results);
    }

    public function addEvent()
    {
        $data = Flight::request()->data;

        if (empty($data->title) || empty($data->start_date)) {
            Flight::halt(400, json_encode(['error' => 'Missing required fields']));
            return;
        }

        $event = new CalendarEvent();
        // $event->widget_id = 'global'; // Removed as per user request
        $event->title = $data->title;
        $event->start_date = $data->start_date;
        $event->end_date = $data->end_date;
        $event->url = $data->url ?? '';
        $event->image = $data->image ?? '';
        $event->description = $data->description ?? '';
        $event->color = $data->color ?? '#3788d8';
        $event->is_visible = $data->is_visible ?? 1;

        $event->save();

        Flight::json($event->getRow());
    }

    public function updateEvent($id)
    {
        $data = Flight::request()->data;
        $model = new CalendarEvent();
        $event = $model->find($id);

        if (!$event) {
            Flight::halt(404, json_encode(['error' => 'Event not found']));
            return;
        }

        // Update fields
        if (isset($data->title))
            $event->title = $data->title;
        if (isset($data->start_date))
            $event->start_date = $data->start_date;
        if (isset($data->end_date))
            $event->end_date = $data->end_date;
        if (isset($data->url))
            $event->url = $data->url;
        if (isset($data->image))
            $event->image = $data->image;
        if (isset($data->description))
            $event->description = $data->description;
        if (isset($data->color))
            $event->color = $data->color;

        $event->updated_at = date('Y-m-d H:i:s');
        $event->save();

        Flight::json($event->getRow());
    }

    public function deleteEvent($id)
    {
        $model = new CalendarEvent();
        $event = $model->find($id);

        if ($event) {
            // Delete support depends on Flight AR implementation, usually simply:
            // Flight's AR wrapper might not have delete() method directly on object?
            // Usually R::trash($bean) or similar.
            // Let's assume standard Flight AR behavior which might rely on underlying database helper or ID.
            // Looking at User model, it extends ActiveRecord.
            // ActiveRecord usually has delete().
            // If not, we might need a raw query.
            // Let's try direct delete assuming standard AR behavior.
            // If it fails, I'll fix it.
            // Alternatively, check codebase for delete examples.
            // I'll take a safe bet and try to use the model's delete/remove capabilities later if this fails.
            // Actually, `flight\ActiveRecord` usually implies `delete()`.

            // Wait, Flight's default AR is simple. Let's assume it maps to proper deletion.
            // ActiveRecord typically has delete() on the instance
            $event->delete();
        } else {
            // If not found via model, try raw delete just in case? No, if not found then 404 or nothing.
            // But for safety if ID exists but AR didn't find it (unlikely), we could use raw SQL.
            // Given the lint error confirms delete() takes 0 args, $event->delete() is correct.
        }

        Flight::json(['status' => 'success']);
    }
}
