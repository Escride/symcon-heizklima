<?php

declare(strict_types=1);

class BBModuleKlima extends IPSModule
{
    private const TEXT_ELEMENTS = [
        'name'       => ['Name', 1.125],
        'actual'     => ['Actual', 0.875],
        'target'     => ['Target', 0.875],
        'output'     => ['Output', 0.875],
        'extra'      => ['Extra', 0.875]
    ];

    private const VARS = [
        'actual' => 'ActualVar',
        'target' => 'TargetVar',
        'output' => 'OutputVar',
        'extra'  => 'ExtraVar'
    ];

    private const DISPLAY_ELEMENTS = [
        'actual' => ['Actual', 'Istwert', 'Target', 'actual', false],
        'target' => ['Target', 'Sollwert', 'Target', 'target', true],
        'output' => ['Output', 'Stellwert / Leistung', 'Output', 'output', false],
        'extra'  => ['Extra', 'Zusatzwert', 'Extra', 'extra', false]
    ];

    private const LOCAL_CONFIGURATION_PROPERTIES = [
        'tile' => 'UseLocalTileConfiguration',
        'actual' => 'UseLocalActualConfiguration',
        'target' => 'UseLocalTargetConfiguration',
        'output' => 'UseLocalOutputConfiguration',
        'extra' => 'UseLocalExtraConfiguration'
    ];

    private const COLOR_PALETTES = [
        'symcon' => [null, '17212B', '2774AE', 'E69A36'],
        'classic' => ['EFE7D3', '392F24', '447744', 'BD542B'],
        'modern' => ['F3F4F6', '111827', '2563EB', 'F97316'],
        'futuristic' => ['0B1020', 'EAF2FF', '00D9FF', '8B5CF6'],
        'neon-green-orange' => ['101710', 'E7FFE5', '39FF14', 'FF6A00'],
        'neon-blue-yellow' => ['0A1020', 'E8F4FF', '00A6FF', 'FFE600'],
        'neon-pink-cyan' => ['180D1A', 'FFF0FF', 'FF2BD6', '00F5FF'],
        'neon-purple-green' => ['120D1D', 'F2EFFF', 'B026FF', '39FF14'],
        'neon-red-cyan' => ['1A0D10', 'FFF0F0', 'FF1744', '00F5FF'],
        'neon-orange-purple' => ['1B1009', 'FFF4E8', 'FF6A00', 'B026FF'],
        'neon-green-pink' => ['0D1711', 'EDFFF2', '39FF14', 'FF2BD6'],
        'neon-blue-orange' => ['0C1220', 'EDF5FF', '00A6FF', 'FF6A00']
    ];

    public function Create()
    {
        parent::Create();
        $this->RegisterVariableString('SharedConfiguration', 'Geteilte Kachelkonfiguration');

        // Namen
        $this->RegisterPropertyString('Name', 'Wohnzimmer');
        $this->RegisterPropertyString('NamePosition', 'top-left');
        $this->RegisterPropertyInteger('NameOffsetX', 0);
        $this->RegisterPropertyInteger('NameOffsetY', 0);

        $defaultPositions = [
            'actual' => [11, 20],
            'target' => [11, 40],
            'output' => [11, 60],
            'extra'  => [11, 80]
        ];
        foreach (self::DISPLAY_ELEMENTS as $key => [$property]) {
            $position = $defaultPositions[$key];
            $this->RegisterPropertyString(
                'Display' . $property . 'Shape',
                in_array($key, ['actual', 'target', 'output'], true) ? 'bar' : 'text'
            );
            $this->RegisterPropertyString('Display' . $property . 'Orientation', 'horizontal');
            $this->RegisterPropertyInteger('Position' . $property . 'X', $position[0]);
            $this->RegisterPropertyInteger('Position' . $property . 'Y', $position[1]);
            $this->RegisterPropertyInteger('Display' . $property . 'Width', 78);
            $this->RegisterPropertyInteger('Display' . $property . 'Angle', 0);
            $this->RegisterPropertyString('Display' . $property . 'TextPosition', 'above');
            $this->RegisterPropertyInteger('Display' . $property . 'StartAngle', 0);
            $this->RegisterPropertyInteger('Display' . $property . 'SweepAngle', 180);
            $this->RegisterPropertyInteger('Display' . $property . 'BorderWidth', 0);
            $this->RegisterPropertyInteger('Display' . $property . 'BorderColor', -1);
            $this->RegisterPropertyInteger('Display' . $property . 'TextOffsetX', 0);
            $this->RegisterPropertyInteger('Display' . $property . 'TextOffsetY', 0);
        }
        $this->RegisterPropertyFloat('RoomControlMin', 5.0);
        $this->RegisterPropertyFloat('RoomControlMax', 35.0);
        $this->RegisterPropertyFloat('RoomControlStep', 0.5);
        $this->RegisterPropertyBoolean('RoomControlEnabled', true);
        $this->RegisterPropertyInteger('PermissionVar', 0);
        $this->RegisterPropertyInteger('RoomPermissionVar', 0);
        foreach (['Output' => [0.0, 100.0, 50.0], 'Extra' => [0.0, 100.0, 50.0]] as $slot => [$min, $max, $mid]) {
            $this->RegisterPropertyFloat('Range' . $slot . 'Min', $min);
            $this->RegisterPropertyFloat('Range' . $slot . 'Max', $max);
            $this->RegisterPropertyFloat('Range' . $slot . 'Mid', $mid);
        }
        $this->RegisterPropertyFloat('RangeRoomTargetMid', 20.0);

        foreach (self::TEXT_ELEMENTS as [$property, $fontSize]) {
            $this->RegisterPropertyInteger('Text' . $property . 'Color', 0);
            $this->RegisterPropertyString('Text' . $property . 'Font', '');
            $this->RegisterPropertyFloat('Text' . $property . 'Size', $fontSize);
            $this->RegisterPropertyInteger('Text' . $property . 'BackgroundColor', -1);
            $this->RegisterPropertyInteger('Text' . $property . 'BorderWidth', 0);
            $this->RegisterPropertyInteger('Text' . $property . 'BorderColor', -1);
            $this->RegisterPropertyInteger('Text' . $property . 'Image', 0);
            $this->RegisterPropertyInteger('Text' . $property . 'ImageOpacity', 100);
        }

        // Variablen
        foreach (self::VARS as $prop) {
            $this->RegisterPropertyInteger($prop, 0);
        }
        $this->RegisterPropertyInteger('GlobalConfigurationVar', 0);
        foreach (self::LOCAL_CONFIGURATION_PROPERTIES as $property) {
            $this->RegisterPropertyBoolean($property, false);
        }

        // Optionen
        foreach (self::DISPLAY_ELEMENTS as [$property]) {
            $this->RegisterPropertyInteger('Color' . $property . 'Low', hexdec('3B82F6'));
            $this->RegisterPropertyInteger('Color' . $property . 'High', hexdec('F97316'));
        }

        // Hintergrund
        $this->RegisterPropertyInteger('BackgroundColor', -1);
        $this->RegisterPropertyInteger('BackgroundOpacity', 100);
        $this->RegisterPropertyInteger('BackgroundImage', 0);
        $this->RegisterPropertyString('ImagePosition', 'full');
        $this->RegisterPropertyInteger('ImageSize', 50);
        $this->RegisterPropertyInteger('ImageOpacity', 40);
        $this->RegisterPropertyString('ColorPalette', 'symcon');

        foreach (self::DISPLAY_ELEMENTS as $key => [$property, $label]) {
            $this->RegisterPropertyString('Label' . $property, $label);
        }

        // Diese Instanz ist eine Kachel-Visualisierung
        $this->SetVisualizationType(1);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        // Alte Nachrichtenregistrierungen entfernen
        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $message) {
                $this->UnregisterMessage($senderID, $message);
            }
        }

        // Neu registrieren
        foreach (self::VARS as $prop) {
            $id = $this->ReadPropertyInteger($prop);
            if ($id > 0 && IPS_VariableExists($id)) {
                $this->RegisterMessage($id, VM_UPDATE);
            }
        }
        $permissionID = $this->ReadPropertyInteger('PermissionVar');
        if ($permissionID <= 0) {
            $permissionID = $this->ReadPropertyInteger('RoomPermissionVar');
        }
        if ($permissionID > 0 && IPS_VariableExists($permissionID)) {
            $this->RegisterMessage($permissionID, VM_UPDATE);
        }

        $configurationID = $this->ReadPropertyInteger('GlobalConfigurationVar');
        if ($configurationID > 0 && IPS_VariableExists($configurationID) &&
            IPS_GetVariable($configurationID)['VariableType'] === 3 &&
            $configurationID !== $this->GetIDForIdent('SharedConfiguration')) {
            $this->RegisterMessage($configurationID, VM_UPDATE);
        }

        $this->PublishConfiguration();
        $this->UpdateVisualizationValue(json_encode($this->GetFullMessage(), JSON_HEX_TAG));
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        if ($Message === VM_UPDATE) {
            if ($SenderID === $this->ReadPropertyInteger('GlobalConfigurationVar')) {
                $this->UpdateVisualizationValue(json_encode($this->GetFullMessage(), JSON_HEX_TAG));
            } else {
                $this->UpdateVisualizationValue(json_encode(['values' => $this->GetValues()], JSON_HEX_TAG));
            }
        }
    }

    // Wird von der Kachel aufgerufen (requestAction in module.html)
    public function RequestAction($Ident, $Value)
    {
        if (strpos((string) $Ident, 'setTarget:') === 0) {
            if (!is_numeric($Value)) {
                throw new InvalidArgumentException('Target values must be numeric.');
            }
            $this->SetTarget(substr((string) $Ident, strlen('setTarget:')), (float) $Value);
            return;
        }
        if (strpos((string) $Ident, 'ApplyLayoutPreset:') === 0) {
            $this->ApplyLayoutPreset(substr((string) $Ident, strlen('ApplyLayoutPreset:')));
            return;
        }

        switch ($Ident) {
            case 'ResetAppearance':
                $this->ResetAppearance();
                break;
            case 'SetPalette':
                if (!is_string($Value)) {
                    throw new InvalidArgumentException('Palette name must be a string.');
                }
                $this->SetPalette($Value);
                break;
        }
    }

    public function GetVisualizationTile()
    {
        $html = file_get_contents(__DIR__ . '/module.html');
        $init = '<script>handleMessage(' . json_encode($this->GetFullMessage(), JSON_HEX_TAG) . ');</script>';
        return $html . $init;
    }

    // ---------------------------------------------------------------

    private function ResetAppearance(): void
    {
        $defaults = [
            'NamePosition'     => 'top-left',
            'NameOffsetX'      => 0,
            'NameOffsetY'      => 0,
            'BackgroundColor'  => -1,
            'BackgroundOpacity' => 100,
            'BackgroundImage'  => 0,
            'ImagePosition'    => 'full',
            'ImageSize'        => 50,
            'ImageOpacity'     => 40,
            'ColorPalette'     => 'symcon'
        ];

        foreach (self::TEXT_ELEMENTS as [$property, $fontSize]) {
            $defaults['Text' . $property . 'Color'] = 0;
            $defaults['Text' . $property . 'Font'] = '';
            $defaults['Text' . $property . 'Size'] = $fontSize;
            $defaults['Text' . $property . 'BackgroundColor'] = -1;
            $defaults['Text' . $property . 'BorderWidth'] = 0;
            $defaults['Text' . $property . 'BorderColor'] = -1;
            $defaults['Text' . $property . 'Image'] = 0;
            $defaults['Text' . $property . 'ImageOpacity'] = 100;
        }
        foreach (self::DISPLAY_ELEMENTS as [$property]) {
            $defaults['Color' . $property . 'Low'] = hexdec('3B82F6');
            $defaults['Color' . $property . 'High'] = hexdec('F97316');
        }

        $defaultPositions = [
            'actual' => [11, 20], 'target' => [11, 40],
            'output' => [11, 60], 'extra' => [11, 80]
        ];
        foreach (self::DISPLAY_ELEMENTS as $key => [$property]) {
            $defaults['Display' . $property . 'Shape'] =
                in_array($key, ['actual', 'target', 'output'], true) ? 'bar' : 'text';
            $defaults['Display' . $property . 'Orientation'] = 'horizontal';
            $defaults['Position' . $property . 'X'] = $defaultPositions[$key][0];
            $defaults['Position' . $property . 'Y'] = $defaultPositions[$key][1];
            $defaults['Display' . $property . 'Width'] = 78;
            $defaults['Display' . $property . 'Angle'] = 0;
            $defaults['Display' . $property . 'TextPosition'] = 'above';
            $defaults['Display' . $property . 'StartAngle'] = 0;
            $defaults['Display' . $property . 'SweepAngle'] = 180;
            $defaults['Display' . $property . 'BorderWidth'] = 0;
            $defaults['Display' . $property . 'BorderColor'] = -1;
            $defaults['Display' . $property . 'TextOffsetX'] = 0;
            $defaults['Display' . $property . 'TextOffsetY'] = 0;
        }
        foreach ($defaults as $property => $value) {
            if (!IPS_SetProperty($this->InstanceID, $property, (string) $value)) {
                throw new RuntimeException('Could not reset appearance property: ' . $property);
            }
        }

        $this->WritePaletteProperties('symcon');
        if (!IPS_ApplyChanges($this->InstanceID)) {
            throw new RuntimeException('Could not apply appearance defaults.');
        }
    }

    private function SetPalette(string $palette): void
    {
        $this->WritePaletteProperties($palette);
        if (!IPS_ApplyChanges($this->InstanceID)) {
            throw new RuntimeException('Could not apply color palette: ' . $palette);
        }
    }

    private function WritePaletteProperties(string $palette): void
    {
        if (!isset(self::COLOR_PALETTES[$palette])) {
            throw new InvalidArgumentException('Unknown color palette: ' . $palette);
        }
        [$background, $text, $low, $high] = self::COLOR_PALETTES[$palette];
        $properties = [
            'ColorPalette' => $palette,
            'BackgroundColor' => $palette === 'symcon' ? -1 : hexdec($background),
            'BackgroundOpacity' => 100
        ];
        foreach (self::DISPLAY_ELEMENTS as [$property]) {
            $properties['Color' . $property . 'Low'] = hexdec($low);
            $properties['Color' . $property . 'High'] = hexdec($high);
        }
        foreach (self::TEXT_ELEMENTS as [$property]) {
            $properties['Text' . $property . 'Color'] = hexdec($text);
        }
        foreach ($properties as $property => $value) {
            if (!IPS_SetProperty($this->InstanceID, $property, (string) $value)) {
                throw new RuntimeException('Could not set palette property: ' . $property);
            }
        }
    }

    private function ApplyLayoutPreset(string $arguments): void
    {
        $layouts = ['bars-stacked', 'bars-side', 'arcs-stacked', 'arcs-side'];
        if (!in_array($arguments, $layouts, true)) {
            throw new InvalidArgumentException('Unknown layout preset: ' . $arguments);
        }
        $keys = array_keys(self::DISPLAY_ELEMENTS);
        $count = count($keys);
        $isArc = strpos($arguments, 'arcs-') === 0;
        $isSide = strpos($arguments, '-side') !== false;
        if ($isSide && $isArc) {
            $positions = [2, 27, 51, 76];
            $yPositions = array_fill(0, $count, 39);
        } elseif ($isSide) {
            $positions = [8, 32, 56, 80];
            $yPositions = array_fill(0, $count, 10);
        } elseif ($isArc) {
            $positions = array_fill(0, $count, 38);
            $yPositions = [3, 27, 51, 75];
        } else {
            $positions = array_fill(0, $count, 10);
            $yPositions = [10, 32, 54, 76];
        }

        foreach ($keys as $index => $key) {
            [$property] = self::DISPLAY_ELEMENTS[$key];
            $settings = [
                'Display' . $property . 'Shape' => $isArc ? 'arc' : 'bar',
                'Display' . $property . 'Orientation' => $isSide && !$isArc ? 'vertical' : 'horizontal',
                'Display' . $property . 'Width' => $isArc
                    ? ($isSide ? 22 : 25)
                    : ($isSide ? 75 : 80),
                'Display' . $property . 'Angle' => 0,
                'Display' . $property . 'TextPosition' => $isArc
                    ? 'inside'
                    : ($isSide ? 'left' : 'above'),
                'Position' . $property . 'X' => $positions[$index],
                'Position' . $property . 'Y' => $yPositions[$index],
                'Display' . $property . 'StartAngle' => 270,
                'Display' . $property . 'SweepAngle' => 180,
                'Display' . $property . 'TextOffsetX' => 0,
                'Display' . $property . 'TextOffsetY' => 0
            ];
            foreach ($settings as $propertyName => $value) {
                if (!IPS_SetProperty($this->InstanceID, $propertyName, (string) $value)) {
                    throw new RuntimeException('Could not apply layout property: ' . $propertyName);
                }
            }
        }
        if (!IPS_ApplyChanges($this->InstanceID)) {
            throw new RuntimeException('Could not apply layout preset: ' . $arguments);
        }
    }

    private function SetTarget(string $key, float $requestedValue): void
    {
        if (!is_finite($requestedValue)) {
            throw new InvalidArgumentException('Target values must be finite.');
        }
        if ($key !== 'target') {
            throw new InvalidArgumentException('Unknown target element: ' . $key);
        }

        $targetConfiguration = $this->GetEffectiveConfiguration()['target'];
        if (!$targetConfiguration['RoomControlEnabled']) {
            return;
        }

        $permissionID = $this->GetPermissionVariableID();
        if ($permissionID > 0 && !$this->IsPermissionGranted($permissionID)) {
            return;
        }

        $min = $targetConfiguration['RoomControlMin'];
        $max = $targetConfiguration['RoomControlMax'];
        $step = $targetConfiguration['RoomControlStep'];
        if ($max <= $min || $step <= 0) {
            throw new RuntimeException('Invalid target range or step configured.');
        }
        $value = max($min, min($max, $requestedValue));
        $value = $min + round(($value - $min) / $step) * $step;
        $value = max($min, min($max, $value));
        $id = $this->ReadPropertyInteger(self::VARS[$key]);
        if ($id <= 0 || !IPS_VariableExists($id)) {
            throw new RuntimeException('The target variable for ' . $key . ' is not configured or does not exist.');
        }
        $variable = IPS_GetVariable($id);
        if ($variable['VariableType'] === 1) {
            RequestAction($id, (int) round($value));
        } elseif ($variable['VariableType'] === 2) {
            RequestAction($id, $value);
        } else {
            throw new RuntimeException('Target variables must be integer or float variables.');
        }
    }

    private function GetValues(): array
    {
        $values = [];
        foreach (self::VARS as $key => $prop) {
            $id = $this->ReadPropertyInteger($prop);
            $values[$key] = ($id > 0 && IPS_VariableExists($id)) ? GetValue($id) : null;
        }
        $permissionID = $this->GetPermissionVariableID();
        $values['permissionAllowed'] = $permissionID <= 0 || $this->IsPermissionGranted($permissionID);
        return $values;
    }

    private function GetFullMessage(): array
    {
        $configuration = $this->GetEffectiveConfiguration();
        $tile = $configuration['tile'];
        $textStyles = [];
        foreach (self::TEXT_ELEMENTS as $key => [$property]) {
            $style = $configuration[$key === 'name' ? 'tile' : $key];
            $fontSize = $style['Text' . $property . 'Size'];
            if ($fontSize > 4) {
                $fontSize /= 16;
            }
            $textStyles[$key] = [
                'color'           => $this->TextColor($style['Text' . $property . 'Color']),
                'fontFamily'      => $style['Text' . $property . 'Font'],
                'fontSize'        => $fontSize,
                'backgroundColor' => $this->Color($style['Text' . $property . 'BackgroundColor'], null),
                'borderWidth'      => $style['Text' . $property . 'BorderWidth'],
                'borderColor'      => $this->Color($style['Text' . $property . 'BorderColor'], null),
                'image'            => [
                    'src'     => $this->GetMediaSource($style['Text' . $property . 'Image']),
                    'opacity' => $style['Text' . $property . 'ImageOpacity']
                ]
            ];
        }

        $elements = [];
        foreach (self::DISPLAY_ELEMENTS as $key => [$property, $defaultLabel, $channel, $valueKey, $isTarget]) {
            $settings = $configuration[$key];
            $targetSettings = $configuration['target'];
            if ($key === 'actual' || $key === 'target') {
                $rangeMin = $targetSettings['RoomControlMin'];
                $rangeMax = $targetSettings['RoomControlMax'];
                $mid = $targetSettings['RangeRoomTargetMid'];
            } else {
                $rangeMin = $settings['Range' . $channel . 'Min'];
                $rangeMax = $settings['Range' . $channel . 'Max'];
                $mid = $settings['Range' . $channel . 'Mid'];
            }
            $element = [
                'label' => $settings['Label' . $property],
                'available' => $this->HasVariable(self::VARS[$valueKey]),
                'valueKey' => $valueKey,
                'target' => $isTarget,
                'rangeMin' => $rangeMin,
                'rangeMax' => $rangeMax,
                'mid' => $mid,
                'shape' => $settings['Display' . $property . 'Shape'],
                'orientation' => $settings['Display' . $property . 'Orientation'],
                'x' => $settings['Position' . $property . 'X'],
                'y' => $settings['Position' . $property . 'Y'],
                'width' => $settings['Display' . $property . 'Width'],
                'angle' => $settings['Display' . $property . 'Angle'],
                'textPosition' => $settings['Display' . $property . 'TextPosition'],
                'start' => $settings['Display' . $property . 'StartAngle'],
                'sweep' => $settings['Display' . $property . 'SweepAngle'],
                'textOffsetX' => $settings['Display' . $property . 'TextOffsetX'],
                'textOffsetY' => $settings['Display' . $property . 'TextOffsetY'],
                'borderWidth' => $settings['Display' . $property . 'BorderWidth'],
                'borderColor' => $this->Color($settings['Display' . $property . 'BorderColor'], null),
                'styles' => ['label' => $textStyles[$key], 'value' => $textStyles[$key]],
                'lowColor' => $this->Color($settings['Color' . $property . 'Low'], '#3B82F6'),
                'highColor' => $this->Color($settings['Color' . $property . 'High'], '#F97316'),
                'step' => 1.0,
                'controllable' => false
            ];

            if ($isTarget) {
                $element['controlMin'] = $targetSettings['RoomControlMin'];
                $element['controlMax'] = $targetSettings['RoomControlMax'];
                $element['step'] = $targetSettings['RoomControlStep'];
                $element['controllable'] = $targetSettings['RoomControlEnabled'];
            }
            $elements[$key] = $element;
        }

        return [
            'config' => [
                'name'         => $tile['Name'],
                'namePosition' => $tile['NamePosition'],
                'nameOffsetX'  => $tile['NameOffsetX'],
                'nameOffsetY'  => $tile['NameOffsetY'],
                'elements' => $elements,
                'textStyles'   => $textStyles,
                'bgColor'      => $this->Color($tile['BackgroundColor'], null),
                'bgOpacity'    => $tile['BackgroundOpacity'],
                'image'        => [
                    'src' => $this->GetMediaSource($tile['BackgroundImage']),
                    'pos' => $tile['ImagePosition'],
                    'size' => $tile['ImageSize'],
                    'opacity' => $tile['ImageOpacity']
                ]
            ],
            'values' => $this->GetValues()
        ];
    }

    private function GetPermissionVariableID(): int
    {
        $id = $this->ReadPropertyInteger('PermissionVar');
        return $id > 0 ? $id : $this->ReadPropertyInteger('RoomPermissionVar');
    }

    private function GetConfigurationSchema(): array
    {
        $tile = [
            'Name' => 'string',
            'NamePosition' => 'string',
            'NameOffsetX' => 'integer',
            'NameOffsetY' => 'integer',
            'BackgroundColor' => 'integer',
            'BackgroundOpacity' => 'integer',
            'BackgroundImage' => 'integer',
            'ImagePosition' => 'string',
            'ImageSize' => 'integer',
            'ImageOpacity' => 'integer',
            'ColorPalette' => 'string'
        ] + $this->GetTextConfigurationProperties('Name');

        $target = $this->GetDisplayConfigurationProperties('Target') + [
            'RoomControlMin' => 'float',
            'RoomControlMax' => 'float',
            'RoomControlStep' => 'float',
            'RoomControlEnabled' => 'boolean',
            'RangeRoomTargetMid' => 'float'
        ];

        return [
            'tile' => $tile,
            'actual' => $this->GetDisplayConfigurationProperties('Actual'),
            'target' => $target,
            'output' => $this->GetDisplayConfigurationProperties('Output') + [
                'RangeOutputMin' => 'float',
                'RangeOutputMax' => 'float',
                'RangeOutputMid' => 'float'
            ],
            'extra' => $this->GetDisplayConfigurationProperties('Extra') + [
                'RangeExtraMin' => 'float',
                'RangeExtraMax' => 'float',
                'RangeExtraMid' => 'float'
            ]
        ];
    }

    private function GetDisplayConfigurationProperties(string $property): array
    {
        return [
            'Label' . $property => 'string',
            'Color' . $property . 'Low' => 'integer',
            'Color' . $property . 'High' => 'integer',
            'Display' . $property . 'Shape' => 'string',
            'Display' . $property . 'Orientation' => 'string',
            'Display' . $property . 'Width' => 'integer',
            'Display' . $property . 'Angle' => 'integer',
            'Display' . $property . 'TextPosition' => 'string',
            'Display' . $property . 'StartAngle' => 'integer',
            'Display' . $property . 'SweepAngle' => 'integer',
            'Display' . $property . 'BorderWidth' => 'integer',
            'Display' . $property . 'BorderColor' => 'integer',
            'Display' . $property . 'TextOffsetX' => 'integer',
            'Display' . $property . 'TextOffsetY' => 'integer',
            'Position' . $property . 'X' => 'integer',
            'Position' . $property . 'Y' => 'integer'
        ] + $this->GetTextConfigurationProperties($property);
    }

    private function GetTextConfigurationProperties(string $property): array
    {
        return [
            'Text' . $property . 'Color' => 'integer',
            'Text' . $property . 'Font' => 'string',
            'Text' . $property . 'Size' => 'float',
            'Text' . $property . 'BackgroundColor' => 'integer',
            'Text' . $property . 'BorderWidth' => 'integer',
            'Text' . $property . 'BorderColor' => 'integer',
            'Text' . $property . 'Image' => 'integer',
            'Text' . $property . 'ImageOpacity' => 'integer'
        ];
    }

    private function ReadConfigurationProperty(string $property, string $type)
    {
        switch ($type) {
            case 'string':
                return $this->ReadPropertyString($property);
            case 'integer':
                return $this->ReadPropertyInteger($property);
            case 'float':
                return $this->ReadPropertyFloat($property);
            case 'boolean':
                return $this->ReadPropertyBoolean($property);
            default:
                throw new LogicException('Unknown configuration property type: ' . $type);
        }
    }

    private function GetLocalConfiguration(): array
    {
        $configuration = [];
        foreach ($this->GetConfigurationSchema() as $group => $properties) {
            foreach ($properties as $property => $type) {
                $configuration[$group][$property] = $this->ReadConfigurationProperty($property, $type);
            }
        }
        return $configuration;
    }

    private function GetEffectiveConfiguration(): array
    {
        $local = $this->GetLocalConfiguration();
        $shared = $this->ReadSharedConfiguration();
        foreach ($local as $group => &$properties) {
            if (!$this->ReadPropertyBoolean(self::LOCAL_CONFIGURATION_PROPERTIES[$group])) {
                $properties = array_replace($properties, $shared[$group] ?? []);
            }
        }
        unset($properties);
        return $local;
    }

    private function ReadSharedConfiguration(): array
    {
        $id = $this->ReadPropertyInteger('GlobalConfigurationVar');
        if ($id <= 0) {
            return [];
        }
        if (!IPS_VariableExists($id)) {
            IPS_LogMessage('BB Module Klima', 'Die gewählte globale Konfigurationsvariable existiert nicht: ' . $id);
            return [];
        }
        if (IPS_GetVariable($id)['VariableType'] !== 3) {
            IPS_LogMessage('BB Module Klima', 'Die globale Konfigurationsvariable muss vom Typ String sein: ' . $id);
            return [];
        }

        $json = GetValue($id);
        if (!is_string($json) || $json === '') {
            IPS_LogMessage('BB Module Klima', 'Die globale Konfigurationsvariable ist leer oder kein gültiger String: ' . $id);
            return [];
        }
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            IPS_LogMessage('BB Module Klima', 'Ungültiges JSON in der globalen Konfiguration ' . $id . ': ' . $exception->getMessage());
            return [];
        }
        if (!is_array($decoded) || ($decoded['version'] ?? null) !== 1 || !is_array($decoded['groups'] ?? null)) {
            IPS_LogMessage('BB Module Klima', 'Unbekanntes Format in der globalen Konfigurationsvariable: ' . $id);
            return [];
        }

        $configuration = [];
        foreach ($this->GetConfigurationSchema() as $group => $properties) {
            $groupValues = $decoded['groups'][$group] ?? null;
            if (!is_array($groupValues)) {
                continue;
            }
            foreach ($properties as $property => $type) {
                if (!array_key_exists($property, $groupValues)) {
                    continue;
                }
                $value = $groupValues[$property];
                $valid = $type === 'string'
                    ? is_string($value)
                    : ($type === 'boolean'
                        ? is_bool($value)
                        : ($type === 'integer'
                            ? is_int($value)
                            : (is_int($value) || (is_float($value) && is_finite($value)))));
                if ($valid) {
                    $configuration[$group][$property] = $value;
                } else {
                    IPS_LogMessage('BB Module Klima', 'Ungültiger Wert für ' . $property . ' in globaler Konfiguration ' . $id);
                }
            }
        }
        return $configuration;
    }

    private function PublishConfiguration(): void
    {
        $id = $this->GetIDForIdent('SharedConfiguration');
        $configuration = [
            'version' => 1,
            'groups' => $this->GetLocalConfiguration()
        ];
        $json = json_encode(
            $configuration,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
        );
        if (GetValue($id) !== $json) {
            if (!SetValueString($id, $json)) {
                throw new RuntimeException('Could not publish shared tile configuration.');
            }
        }
    }

    private function HasVariable(string $property): bool
    {
        $id = $this->ReadPropertyInteger($property);
        return $id > 0 && IPS_VariableExists($id);
    }

    private function IsPermissionGranted(int $id): bool
    {
        if (!IPS_VariableExists($id)) {
            return false;
        }
        $variable = IPS_GetVariable($id);
        return $variable['VariableType'] === 0 && (bool) GetValue($id);
    }

    private function Color(int $color, ?string $fallback): ?string
    {
        return ($color < 0) ? $fallback : sprintf('#%06X', $color);
    }

    private function TextColor(int $color): string
    {
        return ($color < 0) ? 'transparent' : sprintf('#%06X', $color);
    }

    private function GetMediaSource(int $id): string
    {
        if ($id <= 0 || !IPS_MediaExists($id)) {
            return '';
        }

        $media = IPS_GetMedia($id);
        if ($media['MediaType'] !== MEDIATYPE_IMAGE) {
            return '';
        }

        $ext = strtolower(pathinfo($media['MediaFile'], PATHINFO_EXTENSION));
        $mimes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'webp' => 'image/webp'
        ];
        $mime = $mimes[$ext] ?? 'image/png';
        return 'data:' . $mime . ';base64,' . IPS_GetMediaContent($id);
    }
}
