import './project-name-field.css';

interface ProjectNameFieldProps {
  value: string;
  onChange: (value: string) => void;
}

export function ProjectNameField({ value, onChange }: ProjectNameFieldProps) {
  return (
    <label className="project-name-field">
      <span className="project-name-field__label">Project name</span>
      <input
        className="project-name-field__input"
        type="text"
        value={value}
        spellCheck={false}
        autoCapitalize="off"
        autoCorrect="off"
        placeholder="my-app"
        onChange={(e) => onChange(e.currentTarget.value.trim().toLowerCase())}
      />
    </label>
  );
}
