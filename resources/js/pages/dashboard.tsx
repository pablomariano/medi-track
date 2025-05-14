import AppLayout from '@/layouts/app-layout'; // Import the AppLayout component
import { type BreadcrumbItem } from '@/types'; // Import the BreadcrumbItem type
import { Head } from '@inertiajs/react'; // Import the Head component

import { ChartContainer, ChartConfig, ChartLegend, ChartTooltip, ChartLegendContent, ChartTooltipContent } from '@/components/ui/chart'; // Import the ChartContainer, ChartConfig, ChartLegend, ChartTooltip, ChartLegendContent, and ChartTooltipContent components
import { Bar,  BarChart, Line, LineChart, XAxis, YAxis, Tooltip, ResponsiveContainer } from 'recharts'; // Import the Bar, BarChart, Line, LineChart, XAxis, YAxis, Tooltip, and ResponsiveContainer components
import { Card } from '@/components/ui/card'; // Import the Card component
import { Badge } from '@/components/ui/badge'; // Import the Badge component
import { useState, useEffect } from 'react'; // Import the useState and useEffect hooks
import { Button } from '@/components/ui/button'; // Import the Button component
import { RefreshCw } from 'lucide-react'; // Import the RefreshCw icon from Lucide      


const initialChartData = [ // Define the initial chart data
    { month: 'Enero', desktop: 172, mobile: 440 },
    { month: 'Febrero', desktop: 212, mobile: 378 },
    { month: 'Marzo', desktop: 189, mobile: 300 },
    { month: 'Abril', desktop: 120, mobile: 280 },
    { month: 'Mayo', desktop: 110, mobile: 210 },
    { month: 'Junio', desktop: 100, mobile: 245 },
    { month: 'Julio', desktop: 145, mobile: 289 },
    { month: 'Agosto', desktop: 180, mobile: 270 },
    { month: 'Septiembre', desktop: 170, mobile: 260 },
    { month: 'Octubre', desktop: 160, mobile: 250 },
    { month: 'Noviembre', desktop: 150, mobile: 240 },
    { month: 'Diciembre', desktop: 140, mobile: 260 },
];

const chartConfig = { // Define the chart configuration
    desktop: {
      label: "Desktop",
      color: "var(--primary)",
    },
    mobile: {
      label: "Mobile",
      color: "var(--secondary)",
    },
  } satisfies ChartConfig

const statisticsData = [ // Define the statistics data
    { title: 'Total Users', value: '2,543', change: '+12%', trend: 'up' },
    { title: 'Active Sessions', value: '1,234', change: '+5%', trend: 'up' },
    { title: 'Conversion Rate', value: '3.2%', change: '-2%', trend: 'down' },
    { title: 'Avg. Session', value: '4m 32s', change: '+8%', trend: 'up' },
];

const recentActivity = [ // Define the recent activity data
    { id: 1, user: 'John Doe', action: 'Completed profile', time: '2 minutes ago' },
    { id: 2, user: 'Jane Smith', action: 'Started new session', time: '5 minutes ago' },
    { id: 3, user: 'Mike Johnson', action: 'Updated settings', time: '10 minutes ago' },
    { id: 4, user: 'Sarah Wilson', action: 'Logged in', time: '15 minutes ago' },
];

const breadcrumbs: BreadcrumbItem[] = [ // Define the breadcrumbs
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Function to generate random data
const generateRandomData = (min: number, max: number) => {
    return Math.floor(Math.random() * (max - min + 1)) + min;
};

// Function to update chart data
const updateChartData = (data: typeof initialChartData) => {
    return data.map(item => ({
        ...item,
        desktop: generateRandomData(100, 300),
        mobile: generateRandomData(200, 500)
    }));
};

export default function Dashboard() { // Define the Dashboard component
    const [chartData, setChartData] = useState(initialChartData); // Define the chart data state
    const [isLoading, setIsLoading] = useState(false); // Define the isLoading state

    const refreshData = () => { // Define the refreshData function
        setIsLoading(true); // Set the isLoading state to true
        setTimeout(() => { // Set a timeout to update the chart data
            setChartData(prevData => updateChartData(prevData)); // Update the chart data
            setIsLoading(false); // Set the isLoading state to false
        }, 1000); // Set a timeout of 1 second
    };

    useEffect(() => { // Use the useEffect hook to refresh the data every 30 seconds
        const interval = setInterval(refreshData, 12000); // Set an interval to refresh the data every 12 seconds
        return () => clearInterval(interval); // Return a cleanup function to clear the interval
    }, []); // Empty dependency array

    return ( // Return the Dashboard component
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* Statistics Cards */}
                <div className="grid grid-cols-1 gap-4 md:grid-cols-4">
                    {statisticsData.map((stat, index) => (
                        <Card key={index} className="p-4">
                            <div className="flex flex-col">
                                <span className="text-sm text-gray-500">{stat.title}</span>
                                <span className="text-2xl font-bold">{stat.value}</span>
                                <div className="flex items-center gap-2">
                                    <Badge variant={stat.trend === 'up' ? 'default' : 'destructive'}>
                                        {stat.change}
                                    </Badge>
                                </div>
                            </div>
                        </Card>
                    ))}
                </div>

                {/* Charts Section */}
                <div className="grid gap-4 md:grid-cols-2">
                    {/* Bar Chart */}
                    <Card className="p-4">
                        {/* Header */}
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold">Device Usage</h3>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={refreshData}
                                disabled={isLoading}
                            >
                                <RefreshCw className={`mr-2 h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                                Refresh
                            </Button>
                        </div>
                        {/* Bar Chart */}
                        <ChartContainer config={chartConfig} className="min-h-[200px] w-full">
                            <BarChart data={chartData}>
                                <ChartTooltip content={<ChartTooltipContent />} />
                                <ChartLegend content={<ChartLegendContent />} />
                                <XAxis
                                    dataKey="month"
                                    tickLine={false}
                                    tickMargin={10}
                                    axisLine={false}
                                    tickFormatter={(value) => value.slice(0, 3)}
                                    />
                                <Bar dataKey="desktop" fill="var(--color-desktop)" radius={4} />
                                <Bar dataKey="mobile" fill="var(--color-mobile)" radius={4} />
                            </BarChart>
                        </ChartContainer>
                    </Card>

                    {/* Line Chart */}
                    <Card className="p-4">
                        <div className="mb-4 flex items-center justify-between">
                            <h3 className="text-lg font-semibold">Trend Analysis</h3>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={refreshData}
                                disabled={isLoading}
                            >
                                <RefreshCw className={`mr-2 h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                                Refresh
                            </Button>
                        </div>
                        <div className="h-[300px] w-full">
                            <ResponsiveContainer width="90%" height="100%">
                                <LineChart data={chartData}>
                                    <XAxis dataKey="month" />
                                    <YAxis />
                                    <Tooltip />
                                    <Line type="monotone" dataKey="desktop" stroke="var(--primary)" strokeWidth={2} />
                                    <Line type="monotone" dataKey="mobile" stroke="var(--secondary)" strokeWidth={2} />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </Card>
                </div>

                {/* Recent Activity */}
                <Card className="p-4">
                    <h3 className="mb-4 text-lg font-semibold">Recent Activity</h3>
                    <div className="space-y-4">
                        {recentActivity.map((activity) => (
                            <div key={activity.id} className="flex items-center justify-between border-b pb-2">
                                <div>
                                    <span className="font-medium">{activity.user}</span>
                                    <span className="text-gray-500"> {activity.action}</span>
                                </div>
                                <span className="text-sm text-gray-500">{activity.time}</span>
                            </div>
                        ))}
                    </div>
                </Card>
            </div>
        </AppLayout>
    );
}
