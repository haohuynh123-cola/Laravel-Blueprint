import {
  CI_PRESETS,
  DATABASES,
  DOCKER_MODES,
  EXTRAS,
  FRONTEND_STACKS,
  GIT_MODES,
  STARTER_KITS,
  TEST_RUNNERS,
  allowedFrontendStacks,
  type BlueprintConfig,
  type Extra,
  type FrontendStack,
} from '../../lib/blueprint-options';
import { ChoiceGroup } from './ChoiceGroup';
import { ExtrasGroup } from './ExtrasGroup';
import { ProjectNameField } from './ProjectNameField';
import './options-panel.css';

interface OptionsPanelProps {
  config: BlueprintConfig;
  onChange: (next: BlueprintConfig) => void;
}

export function OptionsPanel({ config, onChange }: OptionsPanelProps) {
  const allowedStacks = allowedFrontendStacks(config.starterKit);
  const disabledStackValues = FRONTEND_STACKS.map((s) => s.value).filter(
    (v) => !allowedStacks.includes(v),
  );

  const update = <K extends keyof BlueprintConfig>(key: K, value: BlueprintConfig[K]) => {
    const next: BlueprintConfig = { ...config, [key]: value };

    // If a kit changed, snap the frontend stack to a valid one for that kit.
    if (key === 'starterKit') {
      const allowed = allowedFrontendStacks(value as BlueprintConfig['starterKit']);
      if (!allowed.includes(next.frontendStack)) {
        next.frontendStack = allowed[0] ?? 'none';
      }
    }

    onChange(next);
  };

  const toggleExtra = (extra: Extra) => {
    const next = config.extras.includes(extra)
      ? config.extras.filter((e) => e !== extra)
      : [...config.extras, extra];
    onChange({ ...config, extras: next });
  };

  return (
    <section aria-labelledby="options-heading" className="options-panel">
      <h2 id="options-heading" className="options-panel__heading">
        Configure your project
      </h2>

      <div className="options-panel__groups">
        <ProjectNameField value={config.projectName} onChange={(v) => update('projectName', v)} />

        <ChoiceGroup
          label="Starter kit"
          options={STARTER_KITS}
          value={config.starterKit}
          onChange={(v) => update('starterKit', v)}
        />

        <ChoiceGroup<FrontendStack>
          label="Frontend stack"
          options={FRONTEND_STACKS}
          value={config.frontendStack}
          onChange={(v) => update('frontendStack', v)}
          disabledValues={disabledStackValues}
        />

        <ChoiceGroup
          label="Database"
          options={DATABASES}
          value={config.database}
          onChange={(v) => update('database', v)}
        />

        <ChoiceGroup
          label="Test runner"
          options={TEST_RUNNERS}
          value={config.testRunner}
          onChange={(v) => update('testRunner', v)}
        />

        <ExtrasGroup label="Extras" options={EXTRAS} values={config.extras} onToggle={toggleExtra} />

        <ChoiceGroup
          label="Docker"
          options={DOCKER_MODES}
          value={config.dockerMode}
          onChange={(v) => update('dockerMode', v)}
        />

        <ChoiceGroup
          label="Continuous integration"
          options={CI_PRESETS}
          value={config.ciPreset}
          onChange={(v) => update('ciPreset', v)}
        />

        <ChoiceGroup
          label="Initialize git"
          options={GIT_MODES}
          value={config.gitMode}
          onChange={(v) => update('gitMode', v)}
        />
      </div>
    </section>
  );
}
