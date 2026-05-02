import type { Choice, Extra } from '../../lib/blueprint-options';
import './choice-group.css';

interface ExtrasGroupProps {
  label: string;
  options: ReadonlyArray<Choice<Extra>>;
  values: ReadonlyArray<Extra>;
  onToggle: (value: Extra) => void;
}

export function ExtrasGroup({ label, options, values, onToggle }: ExtrasGroupProps) {
  return (
    <fieldset className="choice-group">
      <legend className="choice-group__legend">{label}</legend>
      <div className="choice-group__options">
        {options.map((opt) => {
          const isActive = values.includes(opt.value);
          return (
            <label
              key={opt.value}
              className={`choice-group__option${isActive ? ' is-active' : ''}`}
            >
              <input
                type="checkbox"
                value={opt.value}
                checked={isActive}
                onChange={() => onToggle(opt.value)}
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
