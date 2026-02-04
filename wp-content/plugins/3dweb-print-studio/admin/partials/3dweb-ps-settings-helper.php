<?php

function dwebps_setting_create_row($label, $description, $name, $value, $type = 'number', $disabled = false)
{
    $checked = '';
    if ($type === 'checkbox') {
        $checked = $value ? 'checked' : '';
        $value   = 1;
    }

    $disabled_attr = $disabled ? 'disabled' : '';

    return sprintf(
        '
            <div class="dweb_ps__settings__row">
            <div style="display: flex; align-items: start;flex-direction: column;">
                    <div class="dweb_ps__settings__label">
                        %s
                    </div>
                         <small class="dweb_ps-settings__settings-holder__description">%s. </small>
                   </div>
                    <div class="dweb_ps__settings-holder">
                        <input class="regular-text ltr" type="%s"
                               name="%s"
                               %s
                               %s
                               value="%s"/>
                   
                    </div>
                </div>
      ',
        esc_html($label), esc_html($description), esc_attr($type), esc_attr($name), $checked, $disabled_attr, esc_attr($value));
}

// create select box
function dwebps_setting_create_select($label, $description, $name, $value, $options)
{
    $select = '<select name="' . esc_attr($name) . '">';
    foreach ($options as $option) {
        $selected = $option['value'] === $value ? 'selected' : '';
        $select .= '<option value="' . esc_attr($option['value']) . '" ' . $selected . '>' . esc_html($option['label']) . '</option>';
    }
    $select .= '</select>';

    return sprintf(
        '
            <div class="dweb_ps__settings__row">
                    <div class="dweb_ps__settings__label">
                        %s
                    </div>
                    <div class="dweb_ps__settings-holder">
                        %s
                        <small class="dweb_ps-settings__settings-holder__description">%s. </small>
                    </div>
                </div>
      ',
        esc_html($label), $select, esc_html($description));
}