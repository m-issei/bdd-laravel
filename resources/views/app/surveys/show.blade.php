<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>{{ $survey->title }}</title>
</head>
<body>
<h1>{{ $survey->title }}</h1>

@if ($response && $response->submitted_at)
    <p>提出済みです。</p>
    <div id="readonly-view">
        @foreach ($survey->questions as $question)
            <div>
                <p>{{ $question->text }}</p>
                @php $answer = $response->answers->firstWhere('question_id', $question->id); @endphp
                <p>{{ $answer?->value ?? '未回答' }}</p>
            </div>
        @endforeach
    </div>
@else
    <div id="answer-form">
        <form method="POST" action="{{ route('app.surveys.submit', $survey->id) }}">
            @csrf
            @foreach ($survey->questions as $question)
                <div>
                    <label>{{ $question->text }}</label>
                    @php $savedAnswer = $response?->answers->firstWhere('question_id', $question->id); @endphp
                    <input type="text" name="answers[{{ $loop->index }}][question_id]" value="{{ $question->id }}" hidden>
                    <input type="text" name="answers[{{ $loop->index }}][value]" value="{{ $savedAnswer?->value ?? '' }}">
                </div>
            @endforeach
            <button type="submit">提出する</button>
        </form>
    </div>
@endif
</body>
</html>
