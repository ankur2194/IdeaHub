import React from 'react';
import {
  DocumentIcon,
  DocumentTextIcon,
  DocumentChartBarIcon,
  PhotoIcon,
  ArchiveBoxIcon,
  ArrowDownTrayIcon,
} from '@heroicons/react/24/outline';

interface Attachment {
  name: string;
  path: string;
  size: number;
  type: string;
}

interface AttachmentListProps {
  attachments: Attachment[];
  onDownload?: (attachment: Attachment) => void;
}

export const AttachmentList: React.FC<AttachmentListProps> = ({
  attachments,
  onDownload,
}) => {
  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
  };

  const getFileIcon = (type: string) => {
    if (type.includes('pdf')) {
      return <DocumentTextIcon className="h-6 w-6 text-red-500" />;
    } else if (type.includes('word') || type.includes('document')) {
      return <DocumentTextIcon className="h-6 w-6 text-blue-500" />;
    } else if (type.includes('sheet') || type.includes('excel')) {
      return <DocumentChartBarIcon className="h-6 w-6 text-green-500" />;
    } else if (type.includes('presentation') || type.includes('powerpoint')) {
      return <DocumentChartBarIcon className="h-6 w-6 text-orange-500" />;
    } else if (type.includes('image')) {
      return <PhotoIcon className="h-6 w-6 text-purple-500" />;
    } else if (type.includes('zip') || type.includes('archive')) {
      return <ArchiveBoxIcon className="h-6 w-6 text-gray-500" />;
    }
    return <DocumentIcon className="h-6 w-6 text-gray-500" />;
  };

  const getFileUrl = (path: string): string => {
    const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000';
    return `${baseUrl}/storage/${path}`;
  };

  const handleDownload = (attachment: Attachment) => {
    if (onDownload) {
      onDownload(attachment);
    } else {
      // Default download behavior
      const link = document.createElement('a');
      link.href = getFileUrl(attachment.path);
      link.download = attachment.name;
      link.target = '_blank';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    }
  };

  if (!attachments || attachments.length === 0) {
    return null;
  }

  return (
    <div className="space-y-2">
      <h3 className="text-sm font-medium text-gray-900">Attachments</h3>
      <div className="space-y-2">
        {attachments.map((attachment, index) => (
          <div
            key={index}
            className="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <div className="flex items-center gap-3 flex-1 min-w-0">
              <div className="flex-shrink-0">{getFileIcon(attachment.type)}</div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-gray-900 truncate">
                  {attachment.name}
                </p>
                <p className="text-xs text-gray-500">
                  {formatFileSize(attachment.size)}
                </p>
              </div>
            </div>
            <button
              onClick={() => handleDownload(attachment)}
              className="ml-3 inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-md text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors"
              title="Download file"
            >
              <ArrowDownTrayIcon className="h-4 w-4" />
              Download
            </button>
          </div>
        ))}
      </div>
    </div>
  );
};
