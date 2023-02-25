<div class="row">
    <div>
        @forelse ($employeeList as $key => $employee)
            <div class="list" role="listbox" tabindex="0" aria-label="email list">
                <ul>
                    <li tabindex="-1" role="option" aria-checked="false">
                        <div class="form-check mb-0 checkbox-style">
                            <input data-id="{{ $employee->employee_id }}" value="{{ $employee->employee_id }}"
                                name="employee_id[]" tabindex="-1" id="employee_id"
                                class="form-check-input employee_id" type="checkbox"
                                {{ strtotime(date('Y-m', strtotime($employee->weekoff_updated_at))) == strtotime(date('Y-m')) ? 'checked' : '' }}>
                            <label for="employee_id" class="form-check-label lable-style"></label>
                            {{ $employee->first_name . ' ' . $employee->last_name . '(' . $employee->finger_id . ')' }}
                        </div>
                    </li>
                </ul>
            </div>
        @empty
            <div class="list" role="listbox" tabindex="0" aria-label="email list">
                <ul>
                    <li>
                        <p><b>No Results Found...</b></p>
                    </li>
                </ul>
            </div>
        @endforelse
    </div>
    <div class="text-right">
        {{ $employeeList->links() }}
    </div>
</div>
