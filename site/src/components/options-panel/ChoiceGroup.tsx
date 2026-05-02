import type { Choice } from '../../lib/blueprint-options';
import './choice-group.css';

interface ChoiceGroupProps<T extends string> {
  label: string;
  options: ReadonlyArray<Choice<T>>;
  value: T;
  onChange: (value: T) => void;
  disabledValues?: ReadonlyArray<T>;
}

export function ChoiceGroup<T extends string>({
  label,
  options,
  value,
  onChange,
  disabledValues = [],
}: ChoiceGroupProps<T>) {
  return (
    <fieldset className="choice-group">
      <legend className="choice-group__legend">{label}</legend>
      <div className="choice-group__options">
        {options.map((opt) => {
          const isDisabled = disabledValues.includes(opt.value);
          const isActive = opt.value === value;
          return (
            <label
              key={opt.value}
              className={`choice-group__option${isActive ? ' is-active' : ''}${
                isDisabled ? ' is-disabled' : ''
              }`}
            >
              <input
                type="radio"
                name={label}
                value={opt.value}
                checked={isActive}
                disabled={isDisabled}
                onChange={() => onChange(opt.value)}
              />
              <span className="choice-group__label">{opt.label}</span>
              {opt.description && (
                <span className="choice-group__description">{opt.description}</span>
              )}
            </label>
          );
        })}
      </div>
    </fieldset>
  );
}
