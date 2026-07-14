<article class="org-node {{ ($type ?? 'faculty') === 'dean' ? 'org-node--dean' : '' }}">
    <div class="org-avatar">
        <div class="org-avatar-inner">
            @if (!empty($person['avatar']))
                <img src="{{ $person['avatar'] }}" alt="{{ $person['name'] }} photo">
            @else
                {{ $person['initials'] }}
            @endif
        </div>
    </div>
    <div class="org-name">{{ $person['name'] }}</div>
    <div class="org-title">{{ $person['job_title'] }}</div>
    <div class="org-department">
        <span class="org-department-label">Department Handled</span>
        {{ $person['department_label'] ?: 'No department listed' }}
    </div>
    <div class="org-meta">
        @if (!empty($person['employee_no']))
            Emp. No. {{ $person['employee_no'] }}<br>
        @endif
        {{ $person['email'] ?: 'No email listed' }}
    </div>
</article>
