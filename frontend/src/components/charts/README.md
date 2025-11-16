# Dashboard Charts Components

This directory contains reusable chart components built with Recharts for data visualization.

## Available Components

### 1. LineChartWidget
Displays data as a line chart, ideal for showing trends over time.

**Props:**
- `data`: Array of data points with x-axis key and numeric values
- `config`: Widget configuration including title, colors, axis keys, etc.

### 2. BarChartWidget
Displays data as a bar chart, ideal for comparisons.

**Props:**
- `data`: Array of data points with x-axis key and numeric values
- `config`: Widget configuration including title, colors, axis keys, etc.

### 3. PieChartWidget
Displays data as a pie chart, ideal for showing distribution.

**Props:**
- `data`: Array of data points with name and value
- `config`: Widget configuration including title, colors, name/data keys, etc.

### 4. AreaChartWidget
Displays data as an area chart, ideal for showing cumulative values.

**Props:**
- `data`: Array of data points with x-axis key and numeric values
- `config`: Widget configuration including title, colors, axis keys, etc.

### 5. StatsCard
Displays a single key metric with optional trend indicator.

**Props:**
- `data`: Stats data including value, label, trend, and color
- `config`: Widget configuration including title

## Usage Example

```tsx
import { LineChartWidget } from './components/charts';

const data = [
  { name: 'Jan', value: 400 },
  { name: 'Feb', value: 300 },
  { name: 'Mar', value: 500 },
];

const config = {
  title: 'Monthly Revenue',
  type: 'line',
  dataSource: 'revenue',
  colors: ['#3b82f6'],
  xAxisKey: 'name',
  showGrid: true,
  showLegend: true,
  showTooltip: true,
};

<LineChartWidget data={data} config={config} />
```

## Integration with DashboardBuilder

These components are designed to work seamlessly with the DashboardBuilder component, which provides:
- Drag-and-drop grid layout
- Widget library panel
- Add/remove widgets
- Resize and reposition widgets
- Save/load dashboard configurations
- Real-time data fetching

See `/frontend/src/pages/DashboardBuilder.tsx` for implementation details.
