<?php

namespace App\Http\Requests;

use App\Exceptions\RespondWithMessageException;
use App\Exceptions\ValidationException;
use App\Models\Course;
use App\Models\LessonStatus;
use App\Models\LessonType;
use App\Models\Section;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class PostLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $route_name = request()->route()->getName();
        $course_id = request()->input('course_id');

        $lessons_count = Course::find($course_id)
            ?->lessons()
            ?->where('is_modification', false)
            ?->count();

        $max_number_in_course = $route_name === 'lessons.create' ? $lessons_count + 1 : $lessons_count;

        return [
            'type_id' => 'required|integer|exists:lesson_types,id',
            'status_id' => 'required|integer|exists:lesson_statuses,id',
            'url' => 'required_if:status_id,==,2|string|max:255',
            'zoom_url' => 'required_if:status_id,==,1|string|max:255',
            'will_at' => 'required_if:status_id,==,1|date|after:' . Carbon::now(),
            'title' => 'required|string|max:255',
            'course_id' => 'required|integer|exists:courses,id',
            'number_in_course' => 'integer|nullable|between:1,' . $max_number_in_course,
            'description' => 'string|max:1024',
            'section_id' => 'required|integer|exists:sections,id',
            // 'preview' => 'required|image|size:15360||dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }

    public function messages()
    {
        return [
            'type_id.required' => 'Укажите тип урока',
            'type_id.integer' => 'Тип урока - целочисленный id',
            'type_id.exists' => 'Тип существующий тип урока',
            'status_id.required' => 'Укажите статус урока',
            'status_id.integer' => 'Статус урока - целочисленный id',
            'status_id.exists' => 'Укажите существующий статус урока',
            'url.required_if' => 'Необходимо вставить ссылку на урок в видеохостинге',
            'url.max' => 'Максимальная длина ссылки - 255 символов',
            'zoom_url.required_if' => 'Необходимо вставить ссылку на урок в Zoom',
            'zoom_url.max' => 'Максимальная длина ссылки - 255 символов',
            'will_at.required_if' => 'Необходимо указать дату проведения урока',
            'will_at.date' => 'Необходимо указать дату проведения в формате timestamp',
            'will_at.after' => 'Укажите дату в будущем',
            'title.required' => 'Укажите название урока',
            'title.max' => 'Максимальная длина названия урока - 255 символов',
            'course_id.required' => 'Укажите курс, для которого создаёте или обновляете урок',
            'course_id.integer' => 'Укажите курс, для которого создаёте или обновляете, - целочисленный id',
            'course_id.exists' => 'Укажите существующий курс, для которого создаёте или обновляете урок',
            'number_in_course.between' => 'Номер урока в курсе должен быть от :min до :max',
            'number_in_course.integer' => 'Укажите номер урока в курсе',
            'description.max' => 'Максимальное количество символов в описании - 1024',
            'section_id.required' => 'Укажите секцию',
            'section_id.integer' => 'Введите id секции в целочисленном формате',
            'section_id.exists' => 'Укажите существующую секцию',
        ];
    }
}
