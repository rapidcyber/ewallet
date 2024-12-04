<?php

namespace App\Traits;
use App\Models\BookingAnswer;
use App\Models\Service;
use Carbon\Carbon;

trait WithServiceInquiryCheck
{
    /**
     * Summary of check_answers
     * @param \App\Models\Service $service
     * @param array $user_answers
     * @return mixed
     */
    public function check_answers(Service $service, array $user_answers): array|string
    {
        $service_questions = $service->form_questions()->with('choices')->get();

        $answers_to_save = [];
        foreach ($service_questions as $service_question) {
            $find = array_search(
                $service_question->question,
                array_column($user_answers, 'question'),
            );
            $answer = $user_answers[$find];

            /// - if a question is important and was not found : return an error
            /// - if a question is not important and was not found : next question
            /// - if a question is not import and found : next step
            /// - if a question is important and found : next step
            if ($service_question->is_important and is_bool($find) and $find == false) {
                return "Question '$service_question->question' is required.";
            }

            if (is_bool($find) and $find == false) {
                continue;
            }

            $ans = new BookingAnswer;
            $ans->question = $answer['question'];
            $ans_answer = ['selected' => $answer['answer']];

            if (in_array($service_question->type, ['dropdown', 'multiple', 'paragraph']) && count($ans_answer['selected']) > 1) {
                return "Only one answer is allowed for question: '$service_question->question'";
            }

            /// Answer's choice must exist
            if (in_array($service_question->type, ['dropdown', 'multiple', 'checkbox'])) {
                $choices = $service_question->choices()->pluck('value')->toArray();
                $diff = array_diff($ans_answer['selected'], $choices);

                if (empty($diff) == false) {
                    return "Invalid answer '" . implode(', ', $diff) . "'. Accepted answers: "
                        . implode("', '", $choices);
                }

                $ans_answer['choices'] = $service_question->choices->pluck('value')->toArray();
            }

            $ans->answer = $ans_answer;
            $ans->type = $service_question->type;
            $ans->is_important = $service_question->is_important;
            array_push($answers_to_save, $ans);
        }

        return $answers_to_save;
    }

    /**
     * Summary of check_service_date_slots
     * @param \App\Models\Service $service
     * @param \Carbon\Carbon $service_date
     * @param array $time_slots
     * @return array|string
     */
    public function check_service_date_slots(Service $service, Carbon $service_date, array $time_slots): array|string
    {
        $day = strtolower($service_date->englishDayOfWeek);

        $service_slots = $service->service_days[$day] ?? null;

        if (empty($service_slots)) {
            return "Invalid service date: '" . $service_date->format('Y-m-d') . "'";
        }

        $slots = [];
        foreach (array_unique($time_slots, SORT_REGULAR) as $time_slot) {
            $start_h = explode(':', $time_slot['start_time'])[0];
            $start_i = explode(':', $time_slot['start_time'])[1];

            $end_h = explode(':', $time_slot['end_time'])[0];
            $end_i = explode(':', $time_slot['end_time'])[1];

            $start = Carbon::create(2000, 1, 1, $start_h, $start_i, 0);
            $end = Carbon::create(2000, 1, 1, $end_h, $end_i, 0);

            $find = array_search(
                [
                    'start_time' => $time_slot['start_time'],
                    'end_time' => $time_slot['end_time']
                ],
                $service_slots
            );

            if ((is_bool($find) and $find == false) || $start->isSameHour($end) || $start->isAfter($end)) {
                return "Invalid time slot: " . $start->format('h:i A') . " to " . $end->format('h:i A');
            }

            array_push($slots, $service_slots[$find]);
        }
        return $slots;
    }
}
