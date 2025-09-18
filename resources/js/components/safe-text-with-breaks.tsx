import { ExternalLink } from 'lucide-react';
import { Fragment } from 'react';

interface SafeTextWithBreaksProps {
    text: string;
    className?: string;
}

export default function SafeTextWithBreaks({ text, className }: SafeTextWithBreaksProps) {
    // Split by newlines and render each line with breaks between them
    const lines = text.split('\n');

    return (
        <span className={className}>
            {lines.map((line, index) => (
                <Fragment key={index}>
                    <LinkifiedText text={line} />
                    {index < lines.length - 1 && <br />}
                </Fragment>
            ))}
        </span>
    );
}

function LinkifiedText({ text }: { text: string }) {
    // URL regex pattern - matches http/https URLs
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    const parts = text.split(urlRegex);

    return (
        <>
            {parts.map((part, index) => {
                if (part.match(urlRegex)) {
                    // This is a URL, make it clickable
                    return (
                        <a
                            key={index}
                            href={part}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline break-all text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            {part}
                            <ExternalLink className="ml-1 inline h-3 w-3" />
                        </a>
                    );
                } else {
                    // Regular text
                    return part;
                }
            })}
        </>
    );
}
