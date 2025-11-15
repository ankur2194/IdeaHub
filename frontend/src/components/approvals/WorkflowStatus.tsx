import React, { useEffect, useState } from 'react';
import { CheckCircleIcon, XCircleIcon, ClockIcon } from '@heroicons/react/24/solid';
import api from '../../services/api';

interface WorkflowLevel {
  level: number;
  status: 'pending' | 'in_progress' | 'approved' | 'rejected';
  approved: number;
  rejected: number;
  pending: number;
  total: number;
  approvals: Array<{
    id: number;
    status: string;
    notes: string | null;
    approved_at: string | null;
    rejected_at: string | null;
    approver: {
      id: number;
      name: string;
      role: string;
    };
  }>;
}

interface WorkflowStatusData {
  current_level: number;
  total_levels: number;
  levels: WorkflowLevel[];
  overall_status: string;
}

interface WorkflowStatusProps {
  ideaId: number;
}

const WorkflowStatus: React.FC<WorkflowStatusProps> = ({ ideaId }) => {
  const [workflow, setWorkflow] = useState<WorkflowStatusData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadWorkflowStatus();
  }, [ideaId]);

  const loadWorkflowStatus = async () => {
    try {
      const response = await api.get(`/ideas/${ideaId}/workflow-status`);
      setWorkflow(response.data.data);
    } catch (error) {
      console.error('Failed to load workflow status:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="rounded-lg border border-gray-200 bg-white p-6">
        <div className="h-6 animate-pulse rounded bg-gray-200"></div>
      </div>
    );
  }

  if (!workflow || workflow.levels.length === 0) {
    return null;
  }

  const getLevelStatusIcon = (status: string) => {
    switch (status) {
      case 'approved':
        return <CheckCircleIcon className="h-6 w-6 text-green-500" />;
      case 'rejected':
        return <XCircleIcon className="h-6 w-6 text-red-500" />;
      case 'in_progress':
        return <ClockIcon className="h-6 w-6 text-yellow-500 animate-pulse" />;
      default:
        return <ClockIcon className="h-6 w-6 text-gray-400" />;
    }
  };

  const getLevelStatusBadge = (status: string) => {
    const badges: Record<string, { bg: string; text: string; label: string }> = {
      approved: { bg: 'bg-green-100', text: 'text-green-800', label: 'Approved' },
      rejected: { bg: 'bg-red-100', text: 'text-red-800', label: 'Rejected' },
      in_progress: { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'In Progress' },
      pending: { bg: 'bg-gray-100', text: 'text-gray-800', label: 'Pending' },
    };

    const badge = badges[status] || badges.pending;

    return (
      <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${badge.bg} ${badge.text}`}>
        {badge.label}
      </span>
    );
  };

  const getApprovalStatusIcon = (status: string) => {
    switch (status) {
      case 'approved':
        return '✓';
      case 'rejected':
        return '✗';
      default:
        return '○';
    }
  };

  return (
    <div className="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
      <h3 className="mb-4 text-lg font-semibold text-gray-900">Approval Workflow</h3>

      {/* Progress Bar */}
      <div className="mb-6">
        <div className="flex items-center justify-between mb-2">
          <span className="text-sm font-medium text-gray-700">
            Level {workflow.current_level} of {workflow.total_levels}
          </span>
          <span className="text-sm text-gray-500">
            {workflow.overall_status}
          </span>
        </div>
        <div className="h-2 w-full rounded-full bg-gray-200">
          <div
            className="h-2 rounded-full bg-blue-600 transition-all duration-300"
            style={{
              width: `${(workflow.current_level / workflow.total_levels) * 100}%`,
            }}
          ></div>
        </div>
      </div>

      {/* Levels */}
      <div className="space-y-4">
        {workflow.levels.map((level) => (
          <div
            key={level.level}
            className={`rounded-lg border p-4 ${
              level.level === workflow.current_level
                ? 'border-blue-300 bg-blue-50'
                : 'border-gray-200 bg-white'
            }`}
          >
            <div className="flex items-start gap-3">
              <div className="flex-shrink-0 mt-0.5">
                {getLevelStatusIcon(level.status)}
              </div>

              <div className="flex-1">
                <div className="flex items-center justify-between mb-2">
                  <h4 className="font-medium text-gray-900">Level {level.level}</h4>
                  {getLevelStatusBadge(level.status)}
                </div>

                <div className="mb-3 text-sm text-gray-600">
                  {level.approved} approved, {level.rejected} rejected, {level.pending} pending
                  of {level.total} approver(s)
                </div>

                {/* Approvers */}
                <div className="space-y-2">
                  {level.approvals.map((approval) => (
                    <div
                      key={approval.id}
                      className="flex items-start gap-2 rounded-md bg-white p-2 text-sm"
                    >
                      <span className="flex-shrink-0 text-lg">
                        {getApprovalStatusIcon(approval.status)}
                      </span>
                      <div className="flex-1 min-w-0">
                        <p className="font-medium text-gray-900">
                          {approval.approver.name}
                        </p>
                        <p className="text-xs text-gray-500 capitalize">
                          {approval.approver.role.replace('_', ' ')}
                        </p>
                        {approval.notes && (
                          <p className="mt-1 text-xs text-gray-600 italic">
                            "{approval.notes}"
                          </p>
                        )}
                        {approval.approved_at && (
                          <p className="text-xs text-green-600">
                            Approved {new Date(approval.approved_at).toLocaleDateString()}
                          </p>
                        )}
                        {approval.rejected_at && (
                          <p className="text-xs text-red-600">
                            Rejected {new Date(approval.rejected_at).toLocaleDateString()}
                          </p>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default WorkflowStatus;
