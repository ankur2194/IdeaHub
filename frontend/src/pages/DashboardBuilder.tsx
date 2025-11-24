import React, { useState, useCallback, useEffect } from 'react';
import { Responsive, WidthProvider } from 'react-grid-layout';
import type { Layout } from 'react-grid-layout';
import {
  PlusIcon,
  TrashIcon,
  ArrowDownTrayIcon,
  ArrowUpTrayIcon,
} from '@heroicons/react/24/outline';
import {
  LineChartWidget,
  BarChartWidget,
  PieChartWidget,
  AreaChartWidget,
  StatsCard,
} from '../components/charts';
import type {
  Dashboard,
  Widget,
  WidgetType,
  DashboardData,
  StatsData,
  ChartDataPoint,
} from '../types/dashboard';
import * as dashboardService from '../services/dashboardService';

// CSS imports for react-grid-layout
import 'react-grid-layout/css/styles.css';
import 'react-resizable/css/styles.css';

const ResponsiveGridLayout = WidthProvider(Responsive);

// Sample data generators for different widget types
const generateSampleData = (type: WidgetType): ChartDataPoint[] | StatsData => {
  switch (type) {
    case 'line':
    case 'bar':
    case 'area':
      return [
        { name: 'Jan', value: 400, ideas: 240 },
        { name: 'Feb', value: 300, ideas: 139 },
        { name: 'Mar', value: 200, ideas: 980 },
        { name: 'Apr', value: 278, ideas: 390 },
        { name: 'May', value: 189, ideas: 480 },
        { name: 'Jun', value: 239, ideas: 380 },
      ];
    case 'pie':
      return [
        { name: 'Approved', value: 400 },
        { name: 'Pending', value: 300 },
        { name: 'Rejected', value: 200 },
        { name: 'In Review', value: 100 },
      ];
    case 'stats':
      return {
        value: '1,234',
        label: 'Total Ideas',
        trend: 12.5,
        trendLabel: 'vs last month',
        color: 'blue',
      };
    default:
      return [];
  }
};

// Widget type definitions
const widgetTypes = [
  { type: 'line' as WidgetType, name: 'Line Chart', icon: '📈' },
  { type: 'bar' as WidgetType, name: 'Bar Chart', icon: '📊' },
  { type: 'pie' as WidgetType, name: 'Pie Chart', icon: '🥧' },
  { type: 'area' as WidgetType, name: 'Area Chart', icon: '📉' },
  { type: 'stats' as WidgetType, name: 'Stats Card', icon: '📋' },
];

export const DashboardBuilder: React.FC = () => {
  const [widgets, setWidgets] = useState<Widget[]>([]);
  const [layouts, setLayouts] = useState<{ [key: string]: Layout[] }>({ lg: [] });
  const [dashboardData, setDashboardData] = useState<DashboardData>({});
  const [showWidgetLibrary, setShowWidgetLibrary] = useState(false);
  const [dashboardName, setDashboardName] = useState<string>('My Dashboard');
  const [currentDashboardId, setCurrentDashboardId] = useState<number | null>(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  // Load dashboard from API on mount
  useEffect(() => {
    loadDashboard();
  }, []);

  const loadDashboard = async () => {
    try {
      setLoading(true);
      const dashboards = await dashboardService.getDashboards();

      // Load default dashboard or first available
      const defaultDashboard = dashboards.find(d => d.is_default) || dashboards[0];

      if (defaultDashboard) {
        setCurrentDashboardId(defaultDashboard.id);
        setDashboardName(defaultDashboard.name);
        setWidgets(defaultDashboard.layout.map(layoutItem => ({
          id: layoutItem.id,
          config: {
            title: layoutItem.title,
            type: layoutItem.type as WidgetType,
            dataSource: 'sample',
            showGrid: true,
            showLegend: true,
            showTooltip: true,
            ...layoutItem.config,
          },
          layout: {
            i: layoutItem.id,
            x: layoutItem.x,
            y: layoutItem.y,
            w: layoutItem.w,
            h: layoutItem.h,
            minW: 2,
            minH: 2,
          },
        })));

        const layoutItems = defaultDashboard.layout.map(l => ({
          i: l.id,
          x: l.x,
          y: l.y,
          w: l.w,
          h: l.h,
        }));
        setLayouts({ lg: layoutItems });
      }
    } catch (error) {
      console.error('Failed to load dashboard:', error);
      // Fall back to empty dashboard
    } finally {
      setLoading(false);
    }
  };

  // Fetch data for widgets (simulated with sample data)
  useEffect(() => {
    const fetchData = () => {
      const newData: DashboardData = {};
      widgets.forEach(widget => {
        newData[widget.id] = generateSampleData(widget.config.type);
      });
      setDashboardData(newData);
    };

    fetchData();
    // Set up auto-refresh if configured
    const intervals = widgets
      .filter(w => w.config.refreshInterval)
      .map(w => {
        return setInterval(() => {
          setDashboardData(prev => ({
            ...prev,
            [w.id]: generateSampleData(w.config.type),
          }));
        }, w.config.refreshInterval! * 1000);
      });

    return () => intervals.forEach(clearInterval);
  }, [widgets]);

  // Add new widget
  const addWidget = useCallback((type: WidgetType) => {
    const id = `widget-${Date.now()}`;
    const newWidget: Widget = {
      id,
      config: {
        title: `New ${type.charAt(0).toUpperCase() + type.slice(1)} Widget`,
        type,
        dataSource: 'sample',
        showGrid: true,
        showLegend: true,
        showTooltip: true,
      },
      layout: {
        i: id,
        x: (widgets.length * 2) % 12,
        y: Infinity, // Puts it at the bottom
        w: type === 'stats' ? 3 : 6,
        h: type === 'stats' ? 2 : 4,
        minW: type === 'stats' ? 2 : 4,
        minH: type === 'stats' ? 2 : 3,
      },
    };

    setWidgets(prev => [...prev, newWidget]);
    setShowWidgetLibrary(false);
  }, [widgets.length]);

  // Remove widget
  const removeWidget = useCallback((id: string) => {
    setWidgets(prev => prev.filter(w => w.id !== id));
    setDashboardData(prev => {
      const newData = { ...prev };
      delete newData[id];
      return newData;
    });
  }, []);

  // Handle layout change
  const onLayoutChange = useCallback((layout: Layout[], layouts: { [key: string]: Layout[] }) => {
    setLayouts(layouts);
    // Update widget layouts
    setWidgets(prev =>
      prev.map(widget => {
        const layoutItem = layout.find(l => l.i === widget.id);
        return layoutItem
          ? {
              ...widget,
              layout: {
                ...widget.layout,
                ...layoutItem,
              },
            }
          : widget;
      })
    );
  }, []);

  // Save dashboard
  const saveDashboard = useCallback(async () => {
    try {
      setSaving(true);
      const layout = widgets.map(w => ({
        id: w.id,
        type: w.config.type,
        title: w.config.title,
        config: w.config,
        x: w.layout.x,
        y: w.layout.y,
        w: w.layout.w,
        h: w.layout.h,
      }));

      if (currentDashboardId) {
        // Update existing dashboard
        await dashboardService.updateDashboard(currentDashboardId, {
          name: dashboardName,
          layout,
        });
      } else {
        // Create new dashboard
        const newDashboard = await dashboardService.createDashboard(dashboardName, layout, true);
        setCurrentDashboardId(newDashboard.id);
      }

      alert('Dashboard saved successfully!');
    } catch (error) {
      console.error('Failed to save dashboard:', error);
      alert('Failed to save dashboard. Please try again.');
    } finally {
      setSaving(false);
    }
  }, [dashboardName, widgets, currentDashboardId]);

  // Export dashboard
  const exportDashboard = useCallback(() => {
    const dashboard: Dashboard = {
      id: 'default',
      name: dashboardName,
      widgets,
      layout: widgets.map(w => w.layout),
      createdAt: new Date().toISOString(),
      updatedAt: new Date().toISOString(),
      isPublic: false,
    };

    const dataStr = JSON.stringify(dashboard, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    const exportFileDefaultName = `dashboard-${Date.now()}.json`;

    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
  }, [dashboardName, widgets]);

  // Import dashboard
  const importDashboard = useCallback((event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const dashboard: Dashboard = JSON.parse(e.target?.result as string);
        setWidgets(dashboard.widgets);
        setDashboardName(dashboard.name);
        const layoutItems = dashboard.widgets.map(w => w.layout);
        setLayouts({ lg: layoutItems });
        alert('Dashboard imported successfully!');
      } catch {
        alert('Failed to import dashboard. Please check the file format.');
      }
    };
    reader.readAsText(file);
  }, []);

  // Render widget based on type
  const renderWidget = (widget: Widget) => {
    const data = dashboardData[widget.id];
    if (!data) return null;

    switch (widget.config.type) {
      case 'line':
        return <LineChartWidget data={data as ChartDataPoint[]} config={widget.config} />;
      case 'bar':
        return <BarChartWidget data={data as ChartDataPoint[]} config={widget.config} />;
      case 'pie':
        return <PieChartWidget data={data as ChartDataPoint[]} config={widget.config} />;
      case 'area':
        return <AreaChartWidget data={data as ChartDataPoint[]} config={widget.config} />;
      case 'stats':
        return <StatsCard data={data as StatsData} config={widget.config} />;
      default:
        return null;
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      {/* Header */}
      <header className="border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div className="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-4">
              <input
                type="text"
                value={dashboardName}
                onChange={(e) => setDashboardName(e.target.value)}
                className="rounded-lg border border-gray-300 px-3 py-2 text-lg font-semibold text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              />
            </div>
            <div className="flex items-center gap-2">
              <button
                onClick={() => setShowWidgetLibrary(!showWidgetLibrary)}
                className="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                <PlusIcon className="h-5 w-5" />
                Add Widget
              </button>
              <button
                onClick={saveDashboard}
                className="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
              >
                <ArrowDownTrayIcon className="h-5 w-5" />
                Save
              </button>
              <button
                onClick={exportDashboard}
                className="rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
              >
                <ArrowUpTrayIcon className="h-5 w-5" />
              </button>
              <label className="cursor-pointer rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <ArrowDownTrayIcon className="h-5 w-5" />
                <input
                  type="file"
                  accept=".json"
                  onChange={importDashboard}
                  className="hidden"
                />
              </label>
            </div>
          </div>
        </div>
      </header>

      {/* Widget Library Panel */}
      {showWidgetLibrary && (
        <div className="border-b border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
          <div className="mx-auto max-w-7xl">
            <h3 className="mb-3 text-sm font-semibold text-gray-900 dark:text-white">
              Choose a widget type:
            </h3>
            <div className="grid grid-cols-5 gap-3">
              {widgetTypes.map(({ type, name, icon }) => (
                <button
                  key={type}
                  onClick={() => addWidget(type)}
                  className="flex flex-col items-center gap-2 rounded-lg border-2 border-gray-200 p-4 transition-colors hover:border-blue-500 hover:bg-blue-50 dark:border-gray-600 dark:hover:border-blue-500 dark:hover:bg-blue-900/20"
                >
                  <span className="text-3xl">{icon}</span>
                  <span className="text-sm font-medium text-gray-900 dark:text-white">{name}</span>
                </button>
              ))}
            </div>
          </div>
        </div>
      )}

      {/* Dashboard Grid */}
      <main className="mx-auto max-w-7xl p-4 sm:px-6 lg:px-8">
        {widgets.length === 0 ? (
          <div className="flex min-h-[400px] items-center justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
            <div className="text-center">
              <h3 className="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
                No widgets yet
              </h3>
              <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Get started by adding a widget to your dashboard.
              </p>
              <div className="mt-6">
                <button
                  onClick={() => setShowWidgetLibrary(true)}
                  className="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                  <PlusIcon className="h-5 w-5" />
                  Add Widget
                </button>
              </div>
            </div>
          </div>
        ) : (
          <ResponsiveGridLayout
            className="layout"
            layouts={layouts}
            breakpoints={{ lg: 1200, md: 996, sm: 768, xs: 480, xxs: 0 }}
            cols={{ lg: 12, md: 10, sm: 6, xs: 4, xxs: 2 }}
            rowHeight={100}
            onLayoutChange={onLayoutChange}
            isDraggable={true}
            isResizable={true}
            compactType="vertical"
            preventCollision={false}
          >
            {widgets.map(widget => (
              <div
                key={widget.id}
                className="relative"
                data-grid={widget.layout}
              >
                <div className="absolute right-2 top-2 z-10 flex gap-1">
                  <button
                    onClick={() => removeWidget(widget.id)}
                    className="rounded-md bg-red-500 p-1.5 text-white opacity-0 transition-opacity hover:bg-red-600 group-hover:opacity-100"
                    style={{ opacity: 1 }}
                  >
                    <TrashIcon className="h-4 w-4" />
                  </button>
                </div>
                <div className="h-full w-full">
                  {renderWidget(widget)}
                </div>
              </div>
            ))}
          </ResponsiveGridLayout>
        )}
      </main>
    </div>
  );
};
