import React from 'react';
import {
  AreaChart,
  Area,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';
import type { ChartDataPoint, WidgetConfig } from '../../types/dashboard';

interface AreaChartWidgetProps {
  data: ChartDataPoint[];
  config: WidgetConfig;
}

export const AreaChartWidget: React.FC<AreaChartWidgetProps> = ({ data, config }) => {
  const {
    title,
    colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'],
    xAxisKey = 'name',
    showGrid = true,
    showLegend = true,
    showTooltip = true,
  } = config;

  // Extract all numeric keys from data to create areas
  const dataKeys = data.length > 0
    ? Object.keys(data[0]).filter(key => key !== xAxisKey && typeof data[0][key] === 'number')
    : [];

  return (
    <div className="h-full w-full rounded-lg bg-white p-4 shadow-md dark:bg-gray-800">
      <h3 className="mb-4 text-lg font-semibold text-gray-900 dark:text-white">{title}</h3>
      <ResponsiveContainer width="100%" height="90%">
        <AreaChart data={data} margin={{ top: 5, right: 30, left: 20, bottom: 5 }}>
          {showGrid && <CartesianGrid strokeDasharray="3 3" className="stroke-gray-200 dark:stroke-gray-700" />}
          <XAxis
            dataKey={xAxisKey}
            className="text-gray-600 dark:text-gray-400"
            stroke="currentColor"
          />
          <YAxis className="text-gray-600 dark:text-gray-400" stroke="currentColor" />
          {showTooltip && (
            <Tooltip
              contentStyle={{
                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                border: '1px solid #e5e7eb',
                borderRadius: '0.5rem',
              }}
            />
          )}
          {showLegend && <Legend />}
          {dataKeys.map((key, index) => (
            <Area
              key={key}
              type="monotone"
              dataKey={key}
              stroke={colors[index % colors.length]}
              fill={colors[index % colors.length]}
              fillOpacity={0.6}
              strokeWidth={2}
            />
          ))}
        </AreaChart>
      </ResponsiveContainer>
    </div>
  );
};
