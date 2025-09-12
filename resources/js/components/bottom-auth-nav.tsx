import { login, register } from '@/routes';
import { Link, usePage } from '@inertiajs/react';
import type { SharedData } from '@/types';
import InlineStatusForm from './inline-status-form';

export default function BottomAuthNav() {
    const { auth } = usePage<SharedData>().props;

    return (
        <div
            className="fixed inset-x-0 bottom-0 z-40 border-t border-[#19140014] bg-[#FDFDFC]/95 backdrop-blur supports-[backdrop-filter]:bg-[#FDFDFC]/80 dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/95 supports-[backdrop-filter]:dark:bg-[#0a0a0a]/80"
            style={{ paddingBottom: 'env(safe-area-inset-bottom)' }}
        >
            <div className="mx-auto flex w-full items-center justify-center gap-3 px-4 py-3 lg:max-w-4xl">
                {auth.user ? (
                    <InlineStatusForm />
                ) : (
                    <>
                        <Link
                            href={register()}
                            className="inline-block rounded-sm border border-[#19140035] px-4 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                        >
                            Register
                        </Link>
                        <Link
                            href={login()}
                            className="inline-block rounded-sm border border-transparent px-4 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                        >
                            Log in
                        </Link>
                    </>
                )}
            </div>
        </div>
    );
}


