<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 18px; }
        body { color: #111; font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        .header { border-bottom: 3px solid #800000; margin-bottom: 8px; padding-bottom: 5px; text-align: center; }
        .university { color: #800000; font-size: 16px; font-weight: bold; }
        .college { font-size: 11px; font-weight: bold; margin-top: 4px; }
        .report-title { color: #78a22f; font-size: 14px; font-weight: bold; margin-top: 6px; }
        .context { border-left: 4px solid #78a22f; margin-bottom: 8px; padding: 6px 8px; }
        .context strong { display: block; margin-bottom: 4px; }
        .context small { color: #555; }
        .tag { background: #f0f5e9; border: 1px solid #d4e2c3; font-size: 12px; font-weight: bold; margin-bottom: 5px; padding: 4px 7px; }
        .tag.missing { color: #777; }
        .tag-section { page-break-inside: auto; }
        .tag-section-new-page { page-break-before: always; }
        .photo-row { font-size: 0; line-height: 0; page-break-inside: avoid; padding: 0; text-align: center; width: 100%; }
        .photo-row img { display: inline-block; height: auto; margin: 0 auto 4px; max-height: 430px; max-width: 100%; vertical-align: middle; }
        .empty { color: #777; padding: 30px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="university">MARINDUQUE STATE UNIVERSITY</div>
        <div class="college">COLLEGE OF INFORMATION AND COMPUTING SCIENCES</div>
        <div class="report-title">PHOTO EVIDENCE</div>
    </div>

    <div class="context">
        <strong>Parameter {{ $parameter->code }}: {{ $parameter->title }}</strong>
        <small>{{ $area->code }} | {{ $category->name }} | {{ $subfolder->code }} - {{ $subfolder->name }}</small>
    </div>

    @php $isFirstEvidenceSection = true; @endphp
    @forelse($photoGroups as $checklistItem => $images)
        <section class="tag-section {{ $isFirstEvidenceSection ? '' : 'tag-section-new-page' }}">
            <div class="tag {{ empty($images) ? 'missing' : '' }}">
                Documents Needed: {{ $checklistItem }}
                @if(empty($images))
                    <span> | No photo captured</span>
                @endif
            </div>
            @if(!empty($images))
                @foreach($images as $image)
                    @php
                        $maxPhotoWidth = 520;
                        $maxPhotoHeight = 360;
                        $photoScale = min($maxPhotoWidth / $image['width'], $maxPhotoHeight / $image['height']);
                        $photoWidth = max(1, (int) round($image['width'] * $photoScale));
                        $photoHeight = max(1, (int) round($image['height'] * $photoScale));
                    @endphp
                    <div class="photo-row"><img src="{{ $image['data'] }}" alt="Photo evidence" style="width: {{ $photoWidth }}px; height: {{ $photoHeight }}px;"></div>
                @endforeach
            @endif
        </section>
        @php $isFirstEvidenceSection = false; @endphp
    @empty
        <div class="tag missing">No document-needed evidence tags are defined for this statement.</div>
        <div class="empty">No supported photo files are available.</div>
    @endforelse
</body>
</html>
