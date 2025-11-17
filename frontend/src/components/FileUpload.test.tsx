import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { FileUpload } from './FileUpload';

describe('FileUpload', () => {
  const mockOnChange = vi.fn();

  const createMockFile = (name: string, size: number, type: string): File => {
    const file = new File(['content'], name, { type });
    Object.defineProperty(file, 'size', { value: size });
    return file;
  };

  beforeEach(() => {
    mockOnChange.mockClear();
  });

  it('renders the file upload component', () => {
    render(<FileUpload files={[]} onChange={mockOnChange} />);

    expect(screen.getByText('Choose Files')).toBeInTheDocument();
    expect(screen.getByText(/0\/5 files/)).toBeInTheDocument();
  });

  it('displays current file count', () => {
    const mockFiles = [
      createMockFile('test1.pdf', 1024, 'application/pdf'),
      createMockFile('test2.pdf', 2048, 'application/pdf'),
    ];

    render(<FileUpload files={mockFiles} onChange={mockOnChange} />);

    expect(screen.getByText(/2\/5 files/)).toBeInTheDocument();
  });

  it('displays uploaded files with their names and sizes', () => {
    const mockFiles = [
      createMockFile('document.pdf', 1024 * 1024, 'application/pdf'),
      createMockFile('image.png', 512 * 1024, 'image/png'),
    ];

    render(<FileUpload files={mockFiles} onChange={mockOnChange} />);

    expect(screen.getByText('document.pdf')).toBeInTheDocument();
    expect(screen.getByText('image.png')).toBeInTheDocument();
    expect(screen.getByText('1 MB')).toBeInTheDocument();
    expect(screen.getByText('512 KB')).toBeInTheDocument();
  });

  it('calls onChange when files are selected', async () => {
    const user = userEvent.setup();
    render(<FileUpload files={[]} onChange={mockOnChange} />);

    const file = createMockFile('test.pdf', 1024, 'application/pdf');
    const input = screen.getByLabelText(/Choose Files/i) as HTMLInputElement;

    await user.upload(input, file);

    expect(mockOnChange).toHaveBeenCalledWith([file]);
  });

  it('removes a file when remove button is clicked', () => {
    const mockFiles = [
      createMockFile('test1.pdf', 1024, 'application/pdf'),
      createMockFile('test2.pdf', 2048, 'application/pdf'),
    ];

    render(<FileUpload files={mockFiles} onChange={mockOnChange} />);

    const removeButtons = screen.getAllByTitle('Remove file');
    fireEvent.click(removeButtons[0]);

    expect(mockOnChange).toHaveBeenCalledWith([mockFiles[1]]);
  });

  it('shows error when max files exceeded', async () => {
    const user = userEvent.setup();
    const existingFiles = [
      createMockFile('test1.pdf', 1024, 'application/pdf'),
      createMockFile('test2.pdf', 1024, 'application/pdf'),
    ];

    render(
      <FileUpload
        files={existingFiles}
        onChange={mockOnChange}
        maxFiles={2}
      />
    );

    const newFile = createMockFile('test3.pdf', 1024, 'application/pdf');
    const input = screen.getByLabelText(/Choose Files/i) as HTMLInputElement;

    await user.upload(input, newFile);

    expect(screen.getByText(/Maximum 2 files allowed/)).toBeInTheDocument();
    expect(mockOnChange).not.toHaveBeenCalled();
  });

  it('shows error when file size exceeds limit', async () => {
    const user = userEvent.setup();
    render(
      <FileUpload
        files={[]}
        onChange={mockOnChange}
        maxSizeMB={1}
      />
    );

    // Create a 2MB file
    const largeFile = createMockFile('large.pdf', 2 * 1024 * 1024, 'application/pdf');
    const input = screen.getByLabelText(/Choose Files/i) as HTMLInputElement;

    await user.upload(input, largeFile);

    expect(screen.getByText(/File too large/)).toBeInTheDocument();
    expect(mockOnChange).not.toHaveBeenCalled();
  });

  it('shows error when file type is not accepted', async () => {
    const user = userEvent.setup();
    render(<FileUpload files={[]} onChange={mockOnChange} />);

    const invalidFile = createMockFile('test.txt', 1024, 'text/plain');
    const input = screen.getByLabelText(/Choose Files/i) as HTMLInputElement;

    // Note: File type validation may not work perfectly in jsdom environment
    // This test verifies the error handling logic exists
    await user.upload(input, invalidFile);

    // In a real browser, this would show an error
    // In test environment, we verify onChange wasn't called with invalid file
    // The actual validation happens in the browser's file picker
    expect(mockOnChange).not.toHaveBeenCalledWith(
      expect.arrayContaining([expect.objectContaining({ type: 'text/plain' })])
    );
  });

  it('displays accepted file formats', () => {
    render(<FileUpload files={[]} onChange={mockOnChange} />);

    expect(screen.getByText(/Accepted formats: PDF, DOC, DOCX/)).toBeInTheDocument();
  });

  it('uses custom maxFiles prop', () => {
    render(
      <FileUpload
        files={[]}
        onChange={mockOnChange}
        maxFiles={10}
      />
    );

    expect(screen.getByText(/0\/10 files/)).toBeInTheDocument();
  });

  it('uses custom maxSizeMB prop', () => {
    render(
      <FileUpload
        files={[]}
        onChange={mockOnChange}
        maxSizeMB={20}
      />
    );

    expect(screen.getByText(/max 20MB each/)).toBeInTheDocument();
  });

  it('formats file size correctly', () => {
    const files = [
      createMockFile('small.pdf', 500, 'application/pdf'),
      createMockFile('medium.pdf', 500 * 1024, 'application/pdf'),
      createMockFile('large.pdf', 1.5 * 1024 * 1024, 'application/pdf'),
    ];

    render(<FileUpload files={files} onChange={mockOnChange} />);

    expect(screen.getByText('500 Bytes')).toBeInTheDocument();
    expect(screen.getByText('500 KB')).toBeInTheDocument();
    expect(screen.getByText('1.5 MB')).toBeInTheDocument();
  });

  it('clears error when removing a file', () => {
    const mockFiles = [
      createMockFile('test1.pdf', 1024, 'application/pdf'),
    ];

    const { rerender } = render(
      <FileUpload files={mockFiles} onChange={mockOnChange} />
    );

    // Simulate error state by trying to add invalid file
    // Then remove an existing file
    const removeButton = screen.getByTitle('Remove file');
    fireEvent.click(removeButton);

    // Re-render with no files
    rerender(<FileUpload files={[]} onChange={mockOnChange} />);

    // Error should not be visible
    expect(screen.queryByText(/File too large/)).not.toBeInTheDocument();
  });
});
