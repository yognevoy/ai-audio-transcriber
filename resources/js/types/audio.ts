/**
 * Audio file status enumeration
 */
export enum AudioFileStatus {
    Uploaded = 'uploaded',
    Processing = 'processing',
    Completed = 'completed',
    Failed = 'failed',
}

/**
 * Transcription status enumeration
 */
export enum TranscriptionStatus {
    Pending = 'pending',
    Processing = 'processing',
    Completed = 'completed',
    Failed = 'failed',
}

/**
 * Audio file interface representing an uploaded audio file
 */
export interface AudioFile {
    id: number | string;
    filename: string;
    size: number;
    size_formatted: string;
    uploaded_at: string;
    status: AudioFileStatus;
    transcription_status?: TranscriptionStatus | null;
    path?: string;
    mime_type?: string;
}

/**
 * Transcription interface representing a transcription result
 */
export interface Transcription {
    id: number | string;
    transcription_id: number | string;
    filename: string;
    content: string;
    raw_content?: string;
    status: TranscriptionStatus;
    error_message?: string | null;
    uploaded_at: string;
    created_at: string;
}

/**
 * File info returned after successful upload
 */
export interface FileInfo {
    id: number | string;
    name: string;
    size: number;
    extension: string;
}

/**
 * API response for file upload
 */
export interface UploadResponse {
    success: boolean;
    message?: string;
    file_info?: FileInfo;
}

/**
 * API response for files list
 */
export interface FilesResponse {
    success: boolean;
    files: AudioFile[];
}

/**
 * API response for transcriptions list
 */
export interface TranscriptionsResponse {
    success: boolean;
    transcriptions: Transcription[];
}

/**
 * API response for delete operation
 */
export interface DeleteResponse {
    success: boolean;
    message: string;
}

/**
 * Status message type for UI display
 */
export type StatusMessageType = 'success' | 'error' | 'info' | 'warning';

/**
 * Status message interface for displaying messages to users
 */
export interface StatusMessage {
    type: StatusMessageType;
    text: string;
    html?: string;
}

/**
 * Tab type for navigation
 */
export type TabType = 'upload' | 'files' | 'transcriptions';
