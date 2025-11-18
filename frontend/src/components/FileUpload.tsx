import React, { useRef, useState } from 'react';
import { DocumentIcon, XMarkIcon } from '@heroicons/react/24/outline';

interface FileUploadProps {
  files: File[];
  onChange: (files: File[]) => void;
  maxFiles?: number;
  maxSizeMB?: number;
  acceptedTypes?: string[];
}

export const FileUpload: React.FC<FileUploadProps> = ({
  files,
  onChange,
  maxFiles = 5,
  maxSizeMB = 10,
  acceptedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/zip',
  ],
}) => {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const [errors, setErrors] = useState<string[]>([]);

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  };

  const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFiles = Array.from(event.target.files || []);
    const newErrors: string[] = [];
    setErrors([]);

    // Validate number of files
    if (files.length + selectedFiles.length > maxFiles) {
      setErrors([`Maximum ${maxFiles} files allowed`]);
      return;
    }

    // Validate file types and sizes
    const validFiles: File[] = [];
    for (const file of selectedFiles) {
      // Check file type
      if (!acceptedTypes.includes(file.type)) {
        newErrors.push(`File type not allowed: ${file.name}`);
        continue;
      }

      // Check file size
      const fileSizeMB = file.size / (1024 * 1024);
      if (fileSizeMB > maxSizeMB) {
        newErrors.push(`File too large: ${file.name} (max ${maxSizeMB}MB)`);
        continue;
      }

      validFiles.push(file);
    }

    // Set all accumulated errors
    if (newErrors.length > 0) {
      setErrors(newErrors);
    }

    // Add valid files even if some failed
    if (validFiles.length > 0) {
      onChange([...files, ...validFiles]);
    }

    // Reset input
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
  };

  const removeFile = (index: number) => {
    const newFiles = files.filter((_, i) => i !== index);
    onChange(newFiles);
    setErrors([]);
  };

  return (
    <div className="space-y-3">
      <div className="flex items-center gap-3">
        <input
          ref={fileInputRef}
          type="file"
          multiple
          accept={acceptedTypes.join(',')}
          onChange={handleFileSelect}
          className="hidden"
          id="file-upload"
        />
        <label
          htmlFor="file-upload"
          className="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <DocumentIcon className="h-5 w-5 mr-2" />
          Choose Files
        </label>
        <span className="text-sm text-gray-500">
          {files.length}/{maxFiles} files (max {maxSizeMB}MB each)
        </span>
      </div>

      {errors.length > 0 && (
        <div className="text-sm text-red-600 bg-red-50 border border-red-200 rounded-md p-3 space-y-1">
          {errors.map((error, index) => (
            <div key={index} className="flex items-start">
              <span className="mr-2">•</span>
              <span>{error}</span>
            </div>
          ))}
        </div>
      )}

      {files.length > 0 && (
        <div className="space-y-2">
          {files.map((file, index) => (
            <div
              key={index}
              className="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-md"
            >
              <div className="flex items-center gap-3 flex-1 min-w-0">
                <DocumentIcon className="h-5 w-5 text-gray-400 flex-shrink-0" />
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-medium text-gray-900 truncate">
                    {file.name}
                  </p>
                  <p className="text-xs text-gray-500">
                    {formatFileSize(file.size)}
                  </p>
                </div>
              </div>
              <button
                type="button"
                onClick={() => removeFile(index)}
                className="ml-3 p-1 rounded-md hover:bg-gray-200 transition-colors"
                title="Remove file"
              >
                <XMarkIcon className="h-5 w-5 text-gray-500" />
              </button>
            </div>
          ))}
        </div>
      )}

      <p className="text-xs text-gray-500">
        Accepted formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, ZIP
      </p>
    </div>
  );
};
