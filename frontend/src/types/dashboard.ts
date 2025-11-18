export interface LayoutItem {
  i: string;
  x: number;
  y: number;
  w: number;
  h: number;
  minW?: number;
  minH?: number;
  maxW?: number;
  maxH?: number;
  static?: boolean;
}

export type WidgetType = 'line' | 'bar' | 'pie' | 'area' | 'stats';

export interface WidgetConfig {
  title: string;
  type: WidgetType;
  dataSource: string;
  refreshInterval?: number;
  colors?: string[];
  xAxisKey?: string;
  yAxisKey?: string;
  dataKey?: string;
  nameKey?: string;
  showGrid?: boolean;
  showLegend?: boolean;
  showTooltip?: boolean;
  customOptions?: Record<string, unknown>;
}

export interface Widget {
  id: string;
  config: WidgetConfig;
  layout: LayoutItem;
}

export interface ChartDataPoint {
  [key: string]: string | number;
}

export interface StatsData {
  value: number | string;
  label: string;
  trend?: number;
  trendLabel?: string;
  icon?: string;
  color?: string;
}

export interface Dashboard {
  id: string;
  name: string;
  description?: string;
  widgets: Widget[];
  layout: LayoutItem[];
  createdAt: string;
  updatedAt: string;
  userId?: string;
  isPublic?: boolean;
}

export interface DashboardData {
  [widgetId: string]: ChartDataPoint[] | StatsData;
}
